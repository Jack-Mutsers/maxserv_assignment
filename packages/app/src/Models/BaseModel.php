<?php

namespace MaxServ\App\Models;

use MaxServ\Core\Database\ConnectionProvider;
use PDO;

class BaseModel
{
    protected string $table;

    public function __construct()
    {
        $this->createTableIfNotExists();
    }

    protected function createTableIfNotExists(): void
    {
        // This method should be implemented in the child classes
    }

    /**
     * Summary of getConnection
     * @return PDO
     */
    protected function getConnection(): PDO
    {
        return ConnectionProvider::getConnection();
    }

    /**
     * Summary of getLatestId
     * @return int
     */
    public function getLatestId(): int
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM {$this->table}");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)($result['max_id'] ?? 0);
    }
}
