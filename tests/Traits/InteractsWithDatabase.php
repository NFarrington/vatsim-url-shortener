<?php

namespace Tests\Traits;

use LaravelDoctrine\ORM\IlluminateRegistry;
use PHPUnit\Framework\Constraint\LogicalNot as ReverseConstraint;
use Tests\Constraints\HasInDatabase;
use Tests\Constraints\SoftDeletedInDatabase;

trait InteractsWithDatabase
{
    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        $this->assertThat(
            $table, new HasInDatabase($this->getConnection($connection), $data)
        );

        return $this;
    }

    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        $constraint = new ReverseConstraint(
            new HasInDatabase($this->getConnection($connection), $data)
        );

        $this->assertThat($table, $constraint);

        return $this;
    }

    /**
     * Assert the given record has been "soft deleted".
     *
     * @param \Illuminate\Database\Eloquent\Model|string $table
     * @param array $data
     * @param string|null $connection
     * @param string|null $deletedAtColumn
     * @return \Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase
     */
    protected function assertSoftDeleted($table, array $data = [], $connection = null, $deletedAtColumn = 'deleted_at')
    {
        //if ($this->isSoftDeletableEntity($table)) {
        //    return $this->assertSoftDeleted($table->getTable(), [$table->getKeyName() => $table->getKey()], $table->getConnectionName(), $table->getDeletedAtColumn());
        //}

        $this->assertThat(
            $table, new SoftDeletedInDatabase($this->getConnection($connection), $data, $deletedAtColumn)
        );

        return $this;
    }

    /**
     * Determine if the argument is a soft deletable model.
     *
     * @param mixed $entity
     * @return bool
     */
    protected function isSoftDeletableEntity($entity)
    {
        return in_array(\App\Entities\Traits\SoftDeletes::class, class_uses_recursive($entity));
    }

    /**
     * Get the database connection.
     *
     * @param string|null $connection
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection($connection = null)
    {
        $registry = app(IlluminateRegistry::class);

        $connection = $connection ?: $registry->getDefaultConnectionName();

        return $registry->getConnection($connection);
    }
}
