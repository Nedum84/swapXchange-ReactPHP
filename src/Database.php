<?php
namespace App;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;

final class Database{
    public $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }
}

?>
