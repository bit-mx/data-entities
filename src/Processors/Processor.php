<?php

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Processor
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
}
