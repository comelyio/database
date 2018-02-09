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

use Comely\IO\Database\Queries\Query;

/**
 * Class Queries
 * @package Comely\IO\Database
 */
class Queries implements \Iterator
{
    /** @var array */
    private $queries;
    /** @var int */
    private $position;

    /**
     * Queries constructor.
     */
    public function __construct()
    {
        $this->queries = [];
        $this->position = 0;
    }

    /**
     * @param Query $query
     * @return Queries
     */
    public function append(Query $query): self
    {
        $this->queries[] = $query;
        return $this;
    }

    /**
     * @return Query|null
     */
    public function last(): ?Query
    {
        $lastQuery = end($this->queries);
        return $lastQuery ? $lastQuery : null;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @return Query
     */
    public function current(): Query
    {
        return $this->queries[$this->position];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->queries[$this->position]);
    }
}