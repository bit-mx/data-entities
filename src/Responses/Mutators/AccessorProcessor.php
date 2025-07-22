<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Responses\Mutators;

use BitMx\DataEntities\PendingQuery;

final readonly class AccessorProcessor
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected array $data,
        protected PendingQuery $pendingQuery,
    ) {}

    /**
     * @return array<array-key, mixed>
     */
    public function process(): array
    {

        $newData = collect($this->data)->mapWithKeys(fn (mixed $value, string $key) => [
            $key => Accessor::make($value, $key, $this->getAccessors(), $this->data)->transform(),
        ]);

        return $newData->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data, PendingQuery $pendingQuery): self
    {
        return new self($data, $pendingQuery);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getAccessors(): array
    {
        return array_merge(
            AccessorsAlias::get(),
            $this->pendingQuery->accessors()->all()
        );
    }
}
