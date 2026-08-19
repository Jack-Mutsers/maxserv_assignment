<?php

declare(strict_types=1);

namespace MaxServ\Core\Database;

use LogicException;
use PDO;

final class ConnectionProvider
{
    private static ?Connection $connection = null;

    public static function setConnection(Connection $connection): void
    {
        self::$connection = $connection;
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            throw new LogicException('The database connection has not been initialized.');
        }

        return self::$connection->getConnection();
    }
}
