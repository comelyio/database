<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Database\Exception;

use Comely\IO\Database\Queries\Query;
use Throwable;

/**
 * Class QueryException
 * @package Comely\IO\Database\Exception
 */
class QueryException extends DatabaseException
{
    /**
     * QueryException constructor.
     * @param Query $query
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Query $query, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->query = $query;
        parent::__construct($message, $code, $previous);
    }
}