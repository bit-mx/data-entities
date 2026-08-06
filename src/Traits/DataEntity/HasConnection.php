<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

trait HasConnection
{
    protected string|Connection|null $connectionOverride = null;

    /**
     * Resolve the configured database connection name or instance.
     *
     * Override to return a Laravel connection name or a live Connection.
     * Runtime overrides via onConnection() take precedence.
     */
    public function resolveDatabaseConnection(): string|Connection
    {
        $connection = config('data-entities.database', 'sqlsrv');

        return is_string($connection) && $connection !== '' ? $connection : 'sqlsrv';
    }

    /**
     * Override the connection for this entity instance (runtime).
     *
     * Precedence: onConnection() > resolveDatabaseConnection() > config.
     */
    public function onConnection(string|Connection $connection): static
    {
        $this->connectionOverride = $connection;

        return $this;
    }

    /**
     * Effective connection before normalizing to a live Connection instance.
     */
    public function resolveEffectiveDatabaseConnection(): string|Connection
    {
        return $this->connectionOverride ?? $this->resolveDatabaseConnection();
    }

    /**
     * Resolve the effective connection to a live Connection instance.
     */
    public function resolveConnection(): Connection
    {
        $resolved = $this->resolveEffectiveDatabaseConnection();

        if ($resolved instanceof Connection) {
            return $resolved;
        }

        return DB::connection($resolved);
    }

    /**
     * Stable string identity for cache keys, list/check commands, and display.
     */
    public function resolveDatabaseConnectionIdentity(): string
    {
        return $this->connectionIdentity($this->resolveEffectiveDatabaseConnection());
    }

    protected function connectionIdentity(string|Connection $connection): string
    {
        if (is_string($connection)) {
            return $connection;
        }

        $name = $connection->getName();

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return $connection->getDriverName().':'.$connection->getDatabaseName();
    }
}
