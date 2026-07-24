<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Testing;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\MockResponseNotFoundException;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use Closure;
use PHPUnit\Framework\Assert;
use ReflectionFunction;
use ReflectionNamedType;

class MockClient
{
    /**
     * @var array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>
     */
    protected array $mockResponses = [];

    /**
     * @var MockResponse|Closure(PendingQuery): MockResponse|null
     */
    protected MockResponse|Closure|null $fallback = null;

    /**
     * @var list<RecordedExecution>
     */
    protected array $recorded = [];

    /**
     * @var array<class-string, int>
     */
    protected array $assertions = [];

    /**
     * @param  array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>  $mockResponses
     */
    public function __construct(array $mockResponses = [])
    {
        $this->mockResponses = $mockResponses;
    }

    /**
     * @param  array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>  $responses
     */
    public function mock(array $responses): self
    {
        $this->mockResponses = array_replace($this->mockResponses, $responses);

        return $this;
    }

    /**
     * @param  MockResponse|Closure(PendingQuery): MockResponse  $response
     */
    public function fallback(MockResponse|Closure $response): self
    {
        $this->fallback = $response;

        return $this;
    }

    /**
     * @return list<RecordedExecution>
     */
    public function recorded(?string $class = null): array
    {
        if ($class === null) {
            return $this->recorded;
        }

        return array_values(array_filter(
            $this->recorded,
            fn (RecordedExecution $execution): bool => $execution->class === $class,
        ));
    }

    /**
     * @return array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>
     */
    public function mockResponses(): array
    {
        return $this->mockResponses;
    }

    public function resolve(string $class, PendingQuery $pendingQuery): MockResponse
    {
        if (array_key_exists($class, $this->mockResponses)) {
            return $this->resolveMockValue($this->mockResponses[$class], $pendingQuery);
        }

        if ($this->fallback !== null) {
            return $this->resolveMockValue($this->fallback, $pendingQuery);
        }

        throw new MockResponseNotFoundException('No mock response found for '.$class);
    }

    public function record(RecordedExecution $execution): void
    {
        $this->recorded[] = $execution;
        $this->assertions[$execution->class] = ($this->assertions[$execution->class] ?? 0) + 1;

        DataEntity::$assertions[$execution->class] = $this->assertions[$execution->class];
        DataEntity::$recordedParameters[$execution->class][] = $execution->parameters;
    }

    /**
     * @param  class-string  $class
     */
    public function assertExecuted(string $class): void
    {
        $count = $this->assertions[$class] ?? 0;

        Assert::assertTrue(
            $count > 0,
            sprintf('Expected [%s] to be executed at least once, but it was not executed.', $class),
        );
    }

    /**
     * @param  class-string  $class
     */
    public function assertExecutedOnce(string $class): void
    {
        $this->assertExecutedCount($class, 1);
    }

    /**
     * @param  class-string  $class
     */
    public function assertExecutedCount(string $class, int $count): void
    {
        $actual = $this->assertions[$class] ?? 0;

        Assert::assertSame(
            $count,
            $actual,
            sprintf('Expected [%s] to be executed %d time(s), but it was executed %d time(s).', $class, $count, $actual),
        );
    }

    /**
     * @param  class-string  $class
     */
    public function assertNotExecuted(string $class): void
    {
        $count = $this->assertions[$class] ?? 0;

        Assert::assertSame(
            0,
            $count,
            sprintf('Expected [%s] not to be executed, but it was executed %d time(s).', $class, $count),
        );
    }

    public function assertNothingExecuted(): void
    {
        Assert::assertSame(
            [],
            $this->recorded,
            sprintf('Expected no Data Entities to be executed, but %d execution(s) were recorded.', count($this->recorded)),
        );
    }

    public function assertTotalExecutedCount(int $count): void
    {
        $actual = count($this->recorded);

        Assert::assertSame(
            $count,
            $actual,
            sprintf('Expected %d total Data Entity execution(s), but %d were recorded.', $count, $actual),
        );
    }

    /**
     * @param  list<class-string>  $classes
     */
    public function assertExecutedInOrder(array $classes): void
    {
        $actual = array_map(
            fn (RecordedExecution $execution): string => $execution->class,
            $this->recorded,
        );

        Assert::assertSame(
            $classes,
            $actual,
            sprintf(
                'Expected Data Entities to be executed in order [%s], but got [%s].',
                implode(', ', $classes),
                implode(', ', $actual),
            ),
        );
    }

    /**
     * @param  class-string  $class
     * @param  array<array-key, mixed>|Closure(array<array-key, mixed>): bool|Closure(RecordedExecution): bool  $expected
     */
    public function assertExecutedWith(string $class, array|Closure $expected): void
    {
        $executions = $this->recorded($class);

        Assert::assertNotEmpty(
            $executions,
            sprintf('Expected [%s] to be executed with matching parameters, but it was not executed.', $class),
        );

        $matched = false;

        foreach ($executions as $execution) {
            if ($this->executionMatches($execution, $expected)) {
                $matched = true;
                break;
            }
        }

        $lastParameters = $executions[count($executions) - 1]->parameters;

        Assert::assertTrue(
            $matched,
            sprintf(
                'Expected [%s] to be executed with matching parameters. Last recorded parameters: %s',
                $class,
                json_encode($lastParameters, JSON_THROW_ON_ERROR),
            ),
        );
    }

    /**
     * @param  MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse  $mock
     */
    protected function resolveMockValue(MockResponse|MockResponseSequence|Closure $mock, PendingQuery $pendingQuery): MockResponse
    {
        if ($mock instanceof Closure) {
            return $mock($pendingQuery);
        }

        if ($mock instanceof MockResponseSequence) {
            return $mock->next();
        }

        return $mock;
    }

    /**
     * @param  array<array-key, mixed>|Closure(array<array-key, mixed>): bool|Closure(RecordedExecution): bool  $expected
     */
    protected function executionMatches(RecordedExecution $execution, array|Closure $expected): bool
    {
        if (! $expected instanceof Closure) {
            return $execution->parametersMatch($expected);
        }

        if ($this->closureExpectsRecordedExecution($expected)) {
            /** @var Closure(RecordedExecution): bool $expected */
            return $expected($execution);
        }

        /** @var Closure(array<array-key, mixed>): bool $expected */
        return $expected($execution->parameters);
    }

    protected function closureExpectsRecordedExecution(Closure $expected): bool
    {
        $parameters = (new ReflectionFunction($expected))->getParameters();

        if ($parameters === []) {
            return false;
        }

        $type = $parameters[0]->getType();

        return $type instanceof ReflectionNamedType
            && $type->getName() === RecordedExecution::class;
    }
}
