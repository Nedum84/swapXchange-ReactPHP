<?php 
require __DIR__ . '/vendor/autoload.php';

use React\Socket\ConnectionInterface;

$loop = React\EventLoop\Factory::create();
$connector = new React\Socket\Connector($loop);
$stdin = new \React\Stream\ReadableResourceStream(STDIN, $loop);

$connector
    ->connect('127.0.0.1:8080')
    ->then(
        function (ConnectionInterface $conn) use ($stdin) {
            $conn->on('data', function($data){
                echo $data;
            });
            $stdin->on('data', function ($data) use ($conn) {
                $conn->write($data);
            });
        },
        function (Exception $exception) use ($loop){
            // reject 
        });

$loop->run();