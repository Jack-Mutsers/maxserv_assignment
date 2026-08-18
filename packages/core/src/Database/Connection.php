<?php

declare(strict_types=1);

namespace MaxServ\Core\Database;

use PDO;

readonly class Connection
{
    private PDO $pdo;

    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database
    ) {
        $this->pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
