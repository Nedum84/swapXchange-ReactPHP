<?php

namespace App\Socket;
use React\Socket\ConnectionInterface;
use React\MySQL\QueryResult;

use App\Database;

class Pool{
    /** @var SplObjectStorage  */
    private $connections;
    private $db;

    public function __construct(Database $db){
        $this->connections = new \SplObjectStorage();
        $this->db = $db->db;
    }

    public function add(ConnectionInterface $connection){
        $connection->write("Enter your name: ");
        $this->initEvents($connection);
        $this->setConnectionData($connection, []);
        
        // $this->connections->attach($connection);
        echo "New connection! ({$connection->getRemoteAddress()})\n";
    }

    /**
     * @param ConnectionInterface $connection
     */
    private function initEvents(ConnectionInterface $connection){
        $this->db->query("SELECT * FROM product")
        ->then(function (QueryResult $queryResult) use ($connection) {
            echo 'SSWWWEWEW';
            $this->sendAll(json_encode($queryResult->resultRows), $connection);
            // $connection->write('json_encode($queryResult->resultRows)');
        },function ($er){
            $this->sendAll(($er));
        });
        return;
        // On receiving the data we loop through other connections
        // from the pool and write this data to them
        $connection->on('data', function ($data) use ($connection) {
            $connectionData = $this->getConnectionData($connection);

            // It is the first data received, so we consider it as
            // a user's name.
            if(empty($connectionData)) {
                $this->addNewMember($data, $connection);
                return;
            }

            $name = $connectionData['name'];
            $this->sendAll("$name: $data", $connection);
        });

        // When connection closes detach it from the pool
        $connection->on('close', function() use ($connection){
            $data = $this->getConnectionData($connection);
            $name = $data['name'] ?? '';

            $this->connections->offsetUnset($connection);
            $this->sendAll("User $name leaves the chat\n", $connection);
        });
    }

    private function addNewMember($name, $connection){
        $name = str_replace(["\n", "\r"], "", $name);
        $this->setConnectionData($connection, ['name' => $name]);
        $this->sendAll("User $name joins the chat\n", $connection);
    }

    private function setConnectionData(ConnectionInterface $connection, $data){
        $this->connections->offsetSet($connection, $data);
    }

    private function getConnectionData(ConnectionInterface $connection) {
        return $this->connections->offsetGet($connection);
    }

    /**
     * Send data to all connections from the pool except
     * the specified one.
     *
     * @param mixed $data
     * @param ConnectionInterface $except
     */
    private function sendAll($data, ConnectionInterface $except) {
        foreach ($this->connections as $conn) {
            // if ($conn != $except) $conn->write($data);
            $conn->write($data);
        }
    }
}