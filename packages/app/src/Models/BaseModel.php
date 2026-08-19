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

    public function loadWithRecord(array $record): void
    {
        // This method should be implemented in the child classes
    }

    /**
     * load a record by its ID and populate the properties of the model
     * @param int $id
     * @return static
     */
    public function load(int $id): static
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $record = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        if (!$record) {
            return new static(); // Return an empty record if not found
        }
        
        // load the record data into the model
        $this->loadWithRecord($record);

        return $this;
    }

    /**
     * get the latest ID from the table
     * @return int
     */
    public function getLatestId(): int
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM {$this->table}");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)($result['max_id'] ?? 0);
    }

    /**
     * get the total number of records in the table
     * @return int
     */
    public function getRecordCount(): int
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$this->table}");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)($result['count'] ?? 0);
    }
}
