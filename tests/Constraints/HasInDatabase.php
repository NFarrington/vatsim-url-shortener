<?php

namespace Tests\Constraints;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Constraint\Constraint;

class HasInDatabase extends Constraint
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
     * Create a new constraint instance.
     *
     * @param \Doctrine\DBAL\Connection $database
     * @param array $data
     * @return void
     */
    public function __construct(Connection $database, array $data)
    {
        $this->data = $data;

        $this->database = $database;
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
            ->select('COUNT(*) as count')->from($table);

        foreach ($this->data as $k => $v) {
            if ($v === null) {
                $query->andWhere("`$k` IS NULL");
            } else {
                $query->andWhere("`$k` = ".$query->createPositionalParameter($v));
            }
        }

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
            "a row in the table [%s] matches the attributes %s.\n\n%s",
            $table, $this->toString(JSON_PRETTY_PRINT), $this->getAdditionalInfo($table)
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
        $query = $this->database->createQueryBuilder()
            ->select('*')->from($table);

        $similarResults = $query->where(
            '`'.array_key_first($this->data).'` = '.$query->createPositionalParameter($this->data[array_key_first($this->data)])
        )->setMaxResults($this->show)->execute()->fetchAll();

        if (!empty($similarResults)) {
            $description = 'Found similar results: '.json_encode($similarResults, JSON_PRETTY_PRINT);
        } else {
            $query = $this->database->createQueryBuilder()
                ->select('*')->from($table);

            $results = $query->setMaxResults($this->show)->execute()->fetchAll();

            if (empty($results)) {
                return 'The table is empty.';
            }

            $description = 'Found: '.json_encode($results, JSON_PRETTY_PRINT);
        }

        $count = (int) $query->select('COUNT(*) as count')->execute()->fetch()['count'];
        if ($count > $this->show) {
            $description .= sprintf(' and %s others', $count - $this->show);
        }

        return $description;
    }

    /**
     * Get a string representation of the object.
     *
     * @param int $options
     * @return string
     */
    public function toString($options = 0): string
    {
        return json_encode($this->data, $options);
    }
}
