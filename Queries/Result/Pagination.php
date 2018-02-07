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

/**
 * Class Pagination
 * @package Comely\IO\Database\Queries\Result
 */
class Pagination
{
    /** @var int */
    public $totalRows;
    /** @var int */
    public $totalPages;
    /** @var int */
    public $start;
    /** @var int */
    public $limit;
    /** @var array */
    public $rows;
    /** @var array */
    public $pages;
    /** @var int */
    public $count;

    /**
     * Pagination constructor.
     *
     * @param int $start
     * @param int $limit
     */
    public function __construct(int $start, int $limit)
    {
        $this->totalRows = 0;
        $this->totalPages = 0;
        $this->count = 0;
        $this->start = $start;
        $this->limit = $limit;
        $this->rows = [];
        $this->pages = [];
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return $this->count;
    }
}