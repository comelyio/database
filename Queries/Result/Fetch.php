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

namespace Comely\IO\Database\Queries\Result;

use Comely\IO\Database\Database;
use Comely\IO\Database\Exception\QueryException;
use Comely\IO\Database\Queries\Query;

/**
 * Class Fetch
 * @package Comely\IO\Database\Queries\Result
 */
class Fetch implements \Countable, \Iterator
{
    /** @var Query */
    private $query;
    /** @var array */
    private $rows;
    /** @var int */
    private $count;
    /** @var int */
    private $index;

    /**
     * Fetch constructor.
     * @param Database $db
     * @param Query $query
     * @throws QueryException
     */
    public function __construct(Database $db, Query $query)
    {
        $this->rows = $db->run(Database::QUERY_FETCH, $query);
        $this->count = count($this->rows);
        $this->index = 0;
        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function query(): Query
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return array|null
     */
    public function first(): ?array
    {
        return $this->rows[0] ?? null;
    }

    /**
     * @return array|null
     */
    public function last(): ?array
    {
        $lastIndex = $this->count - 1;
        return $this->rows[$lastIndex] ?? null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->rows;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @return array
     */
    public function current(): array
    {
        return $this->rows[$this->index];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->rows[$this->index]);
    }
}