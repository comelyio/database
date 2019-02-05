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
use Comely\Kernel\Exception\ComelyException;

/**
 * Class DatabaseException
 * @package Comely\IO\Database\Exception
 */
class DatabaseException extends ComelyException
{
    /** @var null|Query */
    protected $query;

    /**
     * @return Query|null
     */
    public function query(): ?Query
    {
        return $this->query;
    }
}