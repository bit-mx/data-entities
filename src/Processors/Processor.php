<?php

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseTypeEnum;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
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
        $isSuccess = false;
        $exception = null;

        try {
            $data = call_user_func([$this->getClient(), $this->getExecuter()], $this->prepareQuery(), $this->pendingQuery->parameters()->all());

            if (! is_array($data)) {
                $data = [];
            }

            $data = $this->createDataArray($data);

            $isSuccess = true;
        } catch (QueryException $ex) {
            $exception = $ex;
            $isSuccess = false;
        }

        return new Response($this->pendingQuery->getDataEntity(), $data, $isSuccess, $exception);
    }

    protected function getClient(): Connection
    {
        return DB::connection($this->pendingQuery->getDataEntity()->resolveDatabaseConnection());
    }

    protected function getExecuter(): string
    {
        return match ($this->pendingQuery->getMethod()) {
            Method::SELECT => 'select',
            Method::STATEMENT => 'statement',
        };
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function createDataArray(array $data): array
    {
        if ($this->pendingQuery->getDataEntity()->getResponseType() === ResponseTypeEnum::SINGLE) {
            return json_decode((string) json_encode($data[0]), true);
        }

        return json_decode((string) json_encode($data), true);
    }
}
