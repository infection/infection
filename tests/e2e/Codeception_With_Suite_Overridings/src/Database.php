<?php
declare(strict_types=1);

namespace Codeception_With_Suite_Overridings;

class Database
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(string $dsn, string $user, string $password)
    {
        $this->pdo = new \PDO($dsn, $user, $password);
    }

    public function getStuff(int $limit = null)
    {
        if ($limit === null) {
            $statement = $this->pdo->query('SELECT * FROM stuff');
        } else {
            $statement = $this->pdo->query('SELECT * FROM stuff LIMIT ' . $limit);
        }
        return $statement->fetchAll();
    }
}
