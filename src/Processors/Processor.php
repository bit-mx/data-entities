<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Events\DataEntityExecuted;
use BitMx\DataEntities\Events\DataEntityFailed;
use BitMx\DataEntities\Exceptions\MissingRequiredParameterException;
use BitMx\DataEntities\Parameters\ParametersProcessor;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Strategies\Contracts\QueryStrategyContract;
use BitMx\DataEntities\Strategies\LazyQueryStrategy;
use BitMx\DataEntities\Strategies\SimpleQueryStrategy;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\LazyCollection;
use PDOException;

class Processor implements ProcessorContract
{
    use HasQuery;

    protected bool $isSuccess;

    protected string $message;

    /**
     * @var array<array-key, mixed>
     */
    protected array $data = [];

    protected ?\Throwable $exception = null;

    public function __construct(
        protected readonly PendingQuery $pendingQuery,
    ) {}

    public function handle(): Response
    {
        return $this->execute();
    }

    protected function execute(): Response
    {
        return $this->executeStatement();
    }

    protected function executeStatement(): Response
    {
        if (! $this->pendingQuery->usesLazyCollection()) {
            return $this->executeQuery(new SimpleQueryStrategy);
        }

        return $this->executeQuery(new LazyQueryStrategy);
    }

    protected function executeQuery(QueryStrategyContract $strategy): Response
    {
        $data = [];
        $output = [];
        $isSuccess = false;
        $exception = null;
        $lazyCollection = LazyCollection::make();
        $preparedQuery = '';
        $startedAt = hrtime(true);

        try {
            $this->validateRequiredParameters();
            $preparedQuery = $this->prepareQuery();
            $params = $this->createParameters();
            $client = $this->getClient();

            $result = $strategy->execute($client, $preparedQuery, $params);

            if ($result instanceof LazyCollection) {
                $lazyCollection = $result;
            } else {
                $responseData = $this->createDataArray($result);
                $data = $this->createData($responseData);
                $output = $this->createOutput($responseData);
            }

            $isSuccess = true;
        } catch (QueryException|PDOException $ex) {
            $exception = $ex;
        }

        $response = new Response(
            pendingQuery: $this->pendingQuery,
            data: $data,
            output: $output,
            success: $isSuccess,
            senderException: $exception,
            rawLazyData: $lazyCollection,
        );

        $this->dispatchExecutionEvent($response, $preparedQuery, $startedAt, $exception);

        return $response;
    }

    protected function dispatchExecutionEvent(
        Response $response,
        string $query,
        int|float $startedAt,
        ?\Throwable $exception,
    ): void {
        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;
        $dataEntity = $this->pendingQuery->getDataEntity();

        if ($exception !== null) {
            Event::dispatch(new DataEntityFailed(
                dataEntity: $dataEntity,
                pendingQuery: $this->pendingQuery,
                response: $response,
                query: $query,
                durationMs: $durationMs,
                exception: $exception,
            ));

            return;
        }

        Event::dispatch(new DataEntityExecuted(
            dataEntity: $dataEntity,
            pendingQuery: $this->pendingQuery,
            response: $response,
            query: $query,
            durationMs: $durationMs,
        ));
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function createParameters(): array
    {
        $parameters = $this->pendingQuery->parameters();

        $newParameters = (new ParametersProcessor($this->pendingQuery))->process();

        return $newParameters;
    }

    protected function validateRequiredParameters(): void
    {
        $required = $this->pendingQuery->getDataEntity()->requiredParameters();
        $parameters = $this->pendingQuery->parameters();

        foreach ($required as $name) {
            if (! $parameters->toCollection()->has($name)) {
                throw new MissingRequiredParameterException(
                    sprintf('Missing required parameter [%s].', $name)
                );
            }
        }
    }

    protected function getClient(): Connection
    {
        return DB::connection($this->pendingQuery->getDataEntity()->resolveDatabaseConnection());
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function createDataArray(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $data = json_decode((string) json_encode($data), true);

        if ($this->pendingQuery->getResponseType() === ResponseType::SINGLE) {
            return Arr::get($data, '0.0', []);
        }

        return $data;
    }

    /**
     * @param  array<array-key, mixed>  $responseData
     * @return array<array-key, mixed>
     */
    protected function createData(array $responseData): array
    {
        if ($this->pendingQuery->getResponseType() === ResponseType::SINGLE) {
            return $responseData;
        }

        return Arr::get($responseData, '0', []);
    }

    /**
     * @param  array<array-key, mixed>  $responseData
     * @return array<array-key, mixed>
     */
    protected function createOutput(array $responseData): array
    {
        if ($this->pendingQuery->outputParameters()->isEmpty()) {
            return [];
        }

        return collect($responseData)
            ->filter(fn (mixed $value, mixed $key): bool => is_int($key) && $key > 0 && is_array($value))
            ->flatMap(function (array $value): array {
                $firstRow = $value[0] ?? null;

                return is_array($firstRow) ? $firstRow : [];
            })
            ->all();
    }
}
