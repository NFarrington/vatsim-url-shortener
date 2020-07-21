<?php

namespace Tests\Constraints;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Constraint\Constraint;

class SoftDeletedInDatabase extends Constraint
{
    /**
     * Number of records that will be shown in the console in case of failure.
     *
     * @var int
     */
    protected $show = 3;

    /**
     * The database connection.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $database;

    /**
     * The data that will be used to narrow the search in the database table.
     *
     * @var array
     */
    protected $data;

    /**
     * The name of the column that indicates soft deletion has occurred.
     *
     * @var string
     */
    protected $deletedAtColumn;

    /**
     * Create a new constraint instance.
     *
     * @param \Doctrine\DBAL\Connection $database
     * @param array $data
     * @param string $deletedAtColumn
     * @return void
     */
    public function __construct(Connection $database, array $data, string $deletedAtColumn)
    {
        $this->data = $data;

        $this->database = $database;

        $this->deletedAtColumn = $deletedAtColumn;
    }

    /**
     * Check if the data is found in the given table.
     *
     * @param string $table
     * @return bool
     */
    public function matches($table): bool
    {
        $query = $this->database->createQueryBuilder()
            ->select('COUNT(*) as count')
            ->from($table);

        foreach ($this->data as $k => $v) {
            if ($v === null) {
                $query->andWhere("`$k` IS NULL");
            } else {
                $query->andWhere("`$k` = ".$query->createPositionalParameter($v));
            }
        }
        $query->andWhere("`$this->deletedAtColumn` IS NOT NULL");

        return (int) $query->execute()->fetch()['count'] > 0;
    }

    /**
     * Get the description of the failure.
     *
     * @param string $table
     * @return string
     */
    public function failureDescription($table): string
    {
        return sprintf(
            "any soft deleted row in the table [%s] matches the attributes %s.\n\n%s",
            $table, $this->toString(), $this->getAdditionalInfo($table)
        );
    }

    /**
     * Get additional info about the records found in the database table.
     *
     * @param string $table
     * @return string
     */
    protected function getAdditionalInfo($table)
    {
        $query = $this->database->createQueryBuilder()->select('*')->from($table);

        $results = $query->setMaxResults($this->show)->execute()->fetchAll();

        if (empty($results)) {
            return 'The table is empty';
        }

        $description = 'Found: '.json_encode($results, JSON_PRETTY_PRINT);

        $count = (int) $query->select('COUNT(*) as count')->execute()->fetch()['count'];
        if ($count > $this->show) {
            $description .= sprintf(' and %s others', $count - $this->show);
        }

        return $description;
    }

    /**
     * Get a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->data);
    }
}
