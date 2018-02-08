<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Database;

use Comely\IO\Database\Adapter\PDO;
use Comely\IO\Database\Adapter\ServerCredentials;
use Comely\IO\Database\Exception\DatabaseException;
use Comely\IO\Database\Queries\Query;
use Comely\IO\Database\Queries\QueryBuilder;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Database
 * @package Comely\IO\Database
 * @method static Server MySQL
 * @method static Server SQLite
 * @method static Server PgSQL
 * @method static Server PostgreSQL
 */
class Database extends PDO implements ComponentInterface
{
    public const MYSQL = 1001;
    public const SQLITE = 1002;
    public const PGSQL = 1003;

    /** @var ServerCredentials */
    private $server;

    /**
     * Initiate new database connection
     * @param int $driver
     * @return Server
     */
    public static function Server(int $driver): Server
    {
        return new Server($driver);
    }

    /**
     * @param $method
     * @param $arguments
     * @return bool|Server
     */
    public static function __callStatic($method, $arguments)
    {
        switch (strtolower($method)) {
            case "mysql":
                return new Server(self::MYSQL);
            case "sqlite":
                return new Server(self::SQLITE);
            case "pgsql":
            case "postgresql":
                return new Server(self::PGSQL);
        }

        throw new DatabaseException('Only valid database adapters may be called statically');
    }

    /**
     * Database constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        // Get server credentials
        $credentials = $server->getServerCredentials();
        // Construct PDO adapter
        parent::__construct($credentials);

        // Connected, update $server prop
        $this->server = $credentials;
        // Remove sensitive information from credentials
        unset($this->server->username, $this->server->password);
    }

    /**
     * @return ServerCredentials
     */
    public function connection(): ServerCredentials
    {
        return $this->server;
    }

    /**
     * @param string $query
     * @param array ...$data
     * @return array|null
     */
    public function fetch(string $query, ...$data): ?array
    {
        return $this->run(self::QUERY_FETCH, new Query($query, $data));
    }

    /**
     * @param string $query
     * @param array ...$data
     * @return bool
     */
    public function exec(string $query, ...$data): bool
    {
        return $this->run(self::QUERY_EXEC, new Query($query, $data));
    }

    /**
     * @return QueryBuilder
     */
    public function query(): QueryBuilder
    {
        return new QueryBuilder($this);
    }
}