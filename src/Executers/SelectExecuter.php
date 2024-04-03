<?php

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\Contracts\ExecuterContract;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SelectExecuter implements ExecuterContract
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

    #[\Override]
    public function execute(): Response
    {
        $this->create();

        return $this->createResponse();
    }

    protected function create(): void
    {
        try {
            $this->data = DB::connection($this->pendingQuery->getDataEntity()->resolveDatabaseConnection())
                ->select($this->prepareQuery(), $this->getParametersCollection()->all());
            $this->isSuccess = true;
        } catch (QueryException $exception) {
            $this->exception = $exception;
            $this->isSuccess = false;
            $this->message = $exception->getMessage();
        }
    }

    protected function createResponse(): Response
    {
        return new Response($this->pendingQuery->getDataEntity(), $this->data, $this->isSuccess, $this->exception);
    }
}
