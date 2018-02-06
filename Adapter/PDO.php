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

namespace Comely\IO\Database\Adapter;

use Comely\IO\Database\Exception\AdapterException;
use Comely\IO\Database\Exception\ConnectionException;
use Comely\IO\Database\Exception\QueryException;
use Comely\IO\Database\Queries\Query;

/**
 * Class PDO
 * @package Comely\IO\Database\Adapter
 */
abstract class PDO
{
    protected const QUERY_FETCH = 2001;
    protected const QUERY_EXEC = 2002;

    /** @var \PDO */
    private $pdo;
    /** @var bool */
    private $inTransaction;

    /**
     * PDO constructor.
     * @param ServerCredentials $server
     * @throws ConnectionException
     */
    public function __construct(ServerCredentials $server)
    {
        $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
        if ($server->persistent === true) {
            $options[\PDO::ATTR_PERSISTENT] = true;
        }

        try {
            $this->pdo = new \PDO($server->dsn, $server->username, $server->password, $options);
        } catch (\PDOException $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return \PDO
     */
    public function adapter(): \PDO
    {
        return $this->adapter();
    }

    /**
     * @param string|null $name
     * @return int
     * @throws AdapterException
     */
    public function lastInsertId(string $name = null): int
    {
        try {
            $lastInsertId = $this->pdo->lastInsertId($name);
            return intval($lastInsertId);
        } catch (\PDOException $e) {
            throw new AdapterException($e->getMessage());
        }
    }

    /**
     * @return bool
     * @throws AdapterException
     */
    public function inTransaction(): bool
    {
        // Transaction marked locally?
        if ($this->inTransaction) {
            return true;
        }

        try {
            return $this->pdo->inTransaction();
        } catch (\PDOException $e) {
            throw new AdapterException($e->getMessage());
        }
    }

    /**
     * @throws AdapterException
     */
    public function beginTransaction(): void
    {
        try {
            $begin = $this->pdo->beginTransaction();
            if (!$begin) {
                throw new AdapterException('Failed to begin a transaction');
            }

            $this->inTransaction = true;
        } catch (\PDOException $e) {
            throw new AdapterException($e->getMessage());
        }
    }

    /**
     * @throws AdapterException
     */
    public function rollBack(): void
    {
        try {
            $cancel = $this->pdo->rollBack();
            if (!$cancel) {
                throw new AdapterException('Failed to roll back transaction');
            }

            $this->inTransaction = false;
        } catch (\PDOException $e) {
            throw new AdapterException($e->getMessage());
        }
    }

    /**
     * @throws AdapterException
     */
    public function commit(): void
    {
        try {
            $commit = $this->pdo->commit();
            if (!$commit) {
                throw new AdapterException('Failed to commit transaction');
            }

            $this->inTransaction = false;
        } catch (\PDOException $e) {
            throw new AdapterException($e->getMessage());
        }
    }

    /**
     * @param $value
     * @return int
     */
    private function bindValueType($value): int
    {
        $type = gettype($value);
        switch ($type) {
            case "boolean":
                return \PDO::PARAM_BOOL;
            case "integer":
                return \PDO::PARAM_INT;
            case "NULL":
                return \PDO::PARAM_NULL;
            default:
                return \PDO::PARAM_STR;
        }
    }

    /**
     * @param Query $query
     * @param string $error
     * @param int $code
     * @throws QueryException
     */
    private function queryError(Query $query, string $error, int $code = 0): void
    {
        $query->error = $error;
        throw new QueryException($query, $error, $code);
    }

    /**
     * @param int $type
     * @param Query $query
     * @return array|bool|null
     */
    protected function run(int $type, Query $query)
    {
        // Mark query as executed
        $query->executed = true;

        // Execute query
        try {
            // Prepare statement
            $stmnt = $this->pdo->prepare($query->query);

            // Bind params
            foreach ($query->data as $key => $value) {
                if (is_int($key)) {
                    $key++; // Indexed arrays get +1 to numeric keys so they don't start with 0
                }

                $stmnt->bindValue($key, $value, $this->bindValueType($value));
            }

            // Execute
            $exec = $stmnt->execute();
            if (!$exec || $stmnt->errorCode() !== "00000") {
                $this->queryError($query, vsprintf('[%s][%s] %s', $stmnt->errorInfo()));
                return null; // IDE specific
            }

            if ($type === self::QUERY_FETCH) {
                // Fetch Query
                $rows = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
                if (!is_array($rows)) {
                    $this->queryError($query, 'Fetch query failed');
                }

                $query->rows = count($rows);
            } else {
                // Execute Query
                $query->rows = $stmnt->rowCount();
                return true;
            }

        } catch (\PDOException $e) {
            $this->queryError($query, $e->getMessage());
            return null; // IDE specific
        }

        return false;  // IDE specific
    }
}