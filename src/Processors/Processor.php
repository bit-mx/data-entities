<?php

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Parameters\ParametersProcessor;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
    ) {
    }

    public function handle(): Response
    {
        return $this->execute();
    }

    protected function execute(): Response
    {
        $data = [];
        $output = [];

        $isSuccess = false;
        $exception = null;

        try {
            $executionMethod = $this->getExecuter();

            $preparedQuery = $this->prepareQuery();

            $params = $this->createParameters();

            $client = $this->getClient();

            $responseData = $client->$executionMethod($preparedQuery, $params);

            $responseData = is_array($responseData) ? $responseData : [];

            $responseData = $this->createDataArray($responseData);

            $data = $this->createData($responseData);

            $output = $this->createOutput($responseData);

            $isSuccess = true;
        } catch (QueryException $ex) {
            $exception = $ex;
            $isSuccess = false;
        }

        return new Response($this->pendingQuery, $data, $output, $isSuccess, $exception);
    }

    protected function getExecuter(): string
    {
        return match ($this->pendingQuery->getMethod()) {
            Method::SELECT => 'selectResultSets',
            Method::STATEMENT => 'statement',
        };
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
        if (collect($data)->isEmpty()) {
            return [];
        }

        $responseData = json_decode((string) json_encode($data), true);

        if ($this->pendingQuery->getDataEntity()->getResponseType() === ResponseType::SINGLE) {
            return Arr::get($responseData, '0.0', []);
        }

        return $responseData;
    }

    /**
     * @param  array<array-key, mixed>  $responseData
     * @return array<array-key, mixed>
     */
    protected function createData(array $responseData): array
    {
        $dataEntity = $this->pendingQuery->getDataEntity();

        if ($dataEntity->getResponseType() === ResponseType::SINGLE) {
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
