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

namespace Comely\IO\Database\Queries;

use Comely\IO\Database\Database;
use Comely\IO\Database\Exception\QueryException;
use Comely\IO\Database\Queries\Result\Fetch;
use Comely\IO\Database\Queries\Result\Pagination;

/**
 * Class QueryBuilder
 * @package Comely\IO\Database\Queries
 */
class QueryBuilder
{
    /** @var Database */
    private $db;

    /** @var string */
    private $tableName;
    /** @var string */
    private $whereClause;
    /** @var string */
    private $selectColumns;
    /** @var bool */
    private $selectLock;
    /** @var string */
    private $selectOrder;
    /** @var int|null */
    private $selectStart;
    /** @var int|null */
    private $selectLimit;
    /** @var array */
    private $queryData;

    /**
     * QueryBuilder constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->tableName = "";
        $this->whereClause = "1";
        $this->selectColumns = "*";
        $this->selectLock = false;
        $this->selectOrder = "";
        $this->selectStart = null;
        $this->selectLimit = null;
        $this->queryData = [];
    }

    /**
     * @param array $assoc
     * @return bool
     * @throws QueryException
     */
    public function insert(array $assoc): bool
    {
        $query = new Query(sprintf('INSERT' . ' INTO `%s`', $this->tableName), $assoc);
        $cols = [];
        $params = [];

        // Process data
        foreach ($assoc as $key => $value) {
            if (!is_string($key)) {
                throw new QueryException($query, 'INSERT query cannot accept index array');
            }

            $cols[] = sprintf('`%s`', $key);
            $params[] = sprintf(':%s', $key);
        }

        // Compile query
        $query->query .= sprintf(' (%s) VALUES (%s)', implode(",", $cols), implode(",", $params));

        // Run
        $insert = $this->db->run(Database::QUERY_EXEC, $query);
        return $insert;
    }

    /**
     * @param array $assoc
     * @return int
     * @throws QueryException
     */
    public function update(array $assoc): int
    {
        $query = new Query(sprintf('UPDATE' . ' `%s`', $this->tableName), $assoc);
        if ($this->whereClause === "1") {
            throw new QueryException($query, 'UPDATE query requires WHERE clause');
        }

        // SET clause
        $setClause = "";
        foreach ($assoc as $key => $value) {
            if (!is_string($key)) {
                throw new QueryException($query, 'UPDATE query cannot accept index array');
            }

            $setClause .= sprintf('`%1$s`=:%1$s, ', $key);
        }

        // Query Data
        $queryData = $assoc;
        foreach ($this->queryData as $key => $value) {
            if (!is_string($key)) {
                $query->data = $this->queryData;
                throw new QueryException($query, 'WHERE clause for UPDATE query required named parameters');
            }

            // Prefix WHERE clause params with "__"
            $queryData["__" . $key] = $value;
        }

        // Compile Query
        $query->data = $queryData;
        $query->query .= sprintf(
            ' SET %s WHERE %s',
            substr($setClause, 0, -2),
            str_replace(':', ':__', $this->whereClause)
        );

        // Execute UPDATE query
        $update = $this->db->run(Database::QUERY_EXEC, $query);
        return $update === true ? $query->rows : 0;
    }

    /**
     * @return int
     * @throws QueryException
     */
    public function delete(): int
    {
        $query = new Query(sprintf('DELETE FROM' . ' `%s`', $this->tableName), $this->queryData);
        if ($this->whereClause === "1") {
            throw new QueryException($query, 'DELETE query requires WHERE clause');
        }

        $query->query .= sprintf(' WHERE %s', $this->whereClause);

        // Execute
        $delete = $this->db->run(Database::QUERY_EXEC, $query);
        return $delete === true ? $query->rows : 0;
    }

    /**
     * @return Fetch
     * @throws QueryException
     */
    public function fetch(): Fetch
    {
        // Limit
        $limitClause = "";
        if ($this->selectStart && $this->selectLimit) {
            $limitClause = sprintf(' LIMIT %d,%d', $this->selectStart, $this->selectLimit);
        } elseif ($this->selectLimit) {
            $limitClause = sprintf(' LIMIT %d', $this->selectLimit);
        }

        // Query
        $query = new Query(
            sprintf(
                'SELECT' . ' %s FROM `%s` WHERE %s',
                $this->selectColumns,
                $this->tableName,
                $this->whereClause,
                $this->selectOrder,
                $limitClause,
                $this->selectLock ? " FOR UPDATE" : ""
            ),
            $this->queryData
        );

        // Fetch
        return new Fetch($this->db, $query);
    }

    /**
     * @return Pagination
     */
    public function paginate(): Pagination
    {
        // Query pieces
        $this->selectStart = $this->selectStart ?? 0;
        $this->selectLimit = $this->selectLimit ?? 50;

        // Pagination instance
        $pagination = new Pagination($this->selectStart, $this->selectLimit);

        // Find total rows
        $totalRows = $this->db->fetch(
            sprintf('SELECT' . ' count(*) FROM `%s` WHERE %s', $this->tableName, $this->whereClause),
            $this->queryData
        );
        $totalRows = $totalRows[0]["count(*)"] ?? 0;
        if ($totalRows) {
            // Retrieve actual rows falling within limits
            $rowsQuery = sprintf(
                'SELECT' . ' %s FROM `%s` WHERE %s%s LIMIT %d,%d',
                $this->selectColumns,
                $this->tableName,
                $this->whereClause,
                $this->selectOrder,
                $this->selectStart,
                $this->selectLimit
            );

            $pagination->totalRows = $totalRows;
            $pagination->totalPages = ceil($totalRows / $this->selectLimit);
            $pagination->rows = $this->db->fetch($rowsQuery, $this->queryData);
            $pagination->count = count($pagination->rows);

            // Build pages prop.
            for ($i = 0; $i < $pagination->totalPages; $i++) {
                $pagination->pages[] = ["index" => $i + 1, "start" => $i * $this->selectLimit];
            }
        }

        // return Pagination instance
        return $pagination;
    }

    /**
     * @param string $name
     * @return QueryBuilder
     */
    public function table(string $name): self
    {
        $this->tableName = trim($name);
        return $this;
    }

    /**
     * @param string $clause
     * @param array $data
     * @return QueryBuilder
     */
    public function where(string $clause, array $data): self
    {
        $this->whereClause = $clause;
        $this->queryData = $data;
        return $this;
    }

    /**
     * @param array $cols
     * @return QueryBuilder
     */
    public function find(array $cols): self
    {
        // Reset
        $this->whereClause = "";
        $this->queryData = [];

        // Process data
        foreach ($cols as $key => $val) {
            if (!is_string($key)) {
                continue; // skip
            }

            $this->whereClause = sprintf('`%1$s`=:%1$s, ', $key);
            $this->queryData[$key] = $val;
        }

        $this->whereClause = substr($this->whereClause, 0, -2);
        return $this;
    }

    /**
     * @param string[] ...$cols
     * @return QueryBuilder
     */
    public function columns(string ...$cols): self
    {
        $this->selectColumns = implode(",", array_map(function ($col) {
            return preg_match('/[\(|\)]/', $col) ? trim($col) : sprintf('`%1$s`', trim($col));
        }, $cols));
        return $this;
    }

    /**
     * @param string[] ...$cols
     * @return QueryBuilder
     */
    public function select(string ...$cols): self
    {
        return $this->columns(...$cols);
    }

    /**
     * @return QueryBuilder
     */
    public function lock(): self
    {
        $this->selectLock = true;
        return $this;
    }

    /**
     * @param string[] ...$cols
     * @return QueryBuilder
     */
    public function orderAsc(string ...$cols): self
    {
        $cols = array_map(function ($col) {
            return sprintf('`%1$s`', trim($col));
        }, $cols);

        $this->selectOrder = sprintf(" ORDER BY %s ASC", trim(implode(",", $cols), ", "));
        return $this;
    }

    /**
     * @param string[] ...$cols
     * @return QueryBuilder
     */
    public function orderDesc(string ...$cols): self
    {
        $cols = array_map(function ($col) {
            return sprintf('`%1$s`', trim($col));
        }, $cols);

        $this->selectOrder = sprintf(" ORDER BY %s DESC", trim(implode(",", $cols), ", "));
        return $this;
    }

    /**
     * @param int $from
     * @return QueryBuilder
     */
    public function start(int $from): self
    {
        $this->selectStart = $from;
        return $this;
    }

    /**
     * @param int $to
     * @return QueryBuilder
     */
    public function limit(int $to): self
    {
        $this->selectLimit = $to;
        return $this;
    }
}