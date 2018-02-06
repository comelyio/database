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
}