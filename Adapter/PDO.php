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

use Comely\IO\Database\Exception\ConnectionException;
use Comely\IO\Database\Queries\Query;

/**
 * Class PDO
 * @package Comely\IO\Database\Adapter
 */
abstract class PDO
{
    /** @var \PDO */
    protected $pdo;

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
     * @param int $type
     * @param Query $query
     * @return mixed
     */
    protected function run(int $type, Query $query)
    {

    }
}