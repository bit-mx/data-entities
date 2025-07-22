<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\Enums\ResponseType;
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
use Illuminate\Support\LazyCollection;

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

        try {
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
        } catch (QueryException $ex) {
            $exception = $ex;
        }

        return new Response(
            pendingQuery: $this->pendingQuery,
            data: $data,
            output: $output,
            success: $isSuccess,
            senderException: $exception,
            rawLazyData: $lazyCollection,
        );
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
            ->filter(fn (array $value, int $key): bool => $key > 0)
            ->flatMap(fn (array $value): array => $value[0])
            ->all();
    }
}
