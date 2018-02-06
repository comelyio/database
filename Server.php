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

use Comely\IO\Database\Adapter\ServerCredentials;
use Comely\IO\Database\Exception\ConnectionException;

/**
 * Class Server
 * @package Comely\IO\Database
 */
class Server
{
    private const DRIVERS = [
        Database::MYSQL,
        Database::SQLITE,
        Database::PGSQL
    ];

    /** @var int */
    private $driver;
    /** @var null|string */
    private $name;
    /** @var null|string */
    private $host;
    /** @var null|int */
    private $port;
    /** @var null|string */
    private $user;
    /** @var null|string */
    private $pass;
    /** @var null|string */
    private $persistent;

    /**
     * Server constructor.
     * @param int $driver
     */
    public function __construct(int $driver)
    {
        if (!in_array($driver, self::DRIVERS)) {
            throw new ConnectionException('Invalid database driver');
        }

        $this->driver = $driver;
        $this->host = "localhost";
        $this->persistent = false;
    }

    /**
     * @return Database
     */
    public function connect(): Database
    {
        return new Database($this);
    }

    /**
     * @return ServerCredentials
     * @throws ConnectionException
     */
    public function getCredentials(): ServerCredentials
    {
        $credentials = new ServerCredentials();
        $credentials->driver = $this->driver;
        $credentials->driverName = $this->driverName();
        $credentials->dsn = $this->dsn($credentials);
        $credentials->username = $this->user;
        $credentials->password = $this->pass;
        $credentials->persistent = $this->persistent;

        return $credentials;
    }

    /**
     * @param ServerCredentials $credentials
     * @return string
     * @throws ConnectionException
     */
    private function dsn(ServerCredentials $credentials): string
    {
        if ($credentials->driver === Database::SQLITE) {
            return sprintf('sqlite:%s', $this->name);
        }

        // Database name
        if (!$this->name) {
            throw new ConnectionException(
                sprintf('Database name must be specified for driver "%s"', $credentials->driverName)
            );
        }

        $hostname = $this->port ? sprintf('%s;port=%d', $this->host, $this->port) : $this->host;
        return sprintf('%s:host=%s;dbname=%s;charset=utf8mb4', $credentials->driverName, $hostname, $this->name);
    }

    /**
     * @return string
     * @throws ConnectionException
     */
    private function driverName(): string
    {
        // Driver name
        switch ($this->driver) {
            case Database::SQLITE:
                return "sqlite";
            case Database::MYSQL:
                return "mysql";
            case Database::PGSQL:
                return "pgsql";
            default:
                throw new ConnectionException('Database driver unspecified');
        }
    }

    /**
     * @param string $databaseName
     * @return Server
     */
    public function name(string $databaseName): self
    {
        $this->name = $databaseName;
        return $this;
    }

    /**
     * @param string $databaseHost
     * @return Server
     */
    public function host(string $databaseHost): self
    {
        $this->host = $databaseHost;
        return $this;
    }

    /**
     * @param int $port
     * @return Server
     */
    public function port(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string $user
     * @return Server
     */
    public function username(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $password
     * @return Server
     */
    public function password(string $password): self
    {
        $this->pass = $password;
        return $this;
    }

    /**
     * @return Server
     */
    public function persistent(): self
    {
        $this->persistent = true;
        return $this;
    }
}