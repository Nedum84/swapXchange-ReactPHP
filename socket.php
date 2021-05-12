<?php
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Http\Server;
use React\MySQL\Factory;

use App\JWTAuth\JwtAuthenticator;
use App\JWTAuth\JwtEncoder;
use App\JWTAuth\Guard;


require __DIR__ . '/vendor/autoload.php';

$user = "ned";
$pass = "ned";
$db = "swapxchange";
$host = "lacalhost";
$host = "192.168.64.2";

$url = "$user:$pass@$host/$db";

$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
$db = $factory->createLazyConnection($url);
$dbCon = new \App\Database($db);

//open the socket
$socket = new \React\Socket\Server(8080, $loop);

$pool = new \App\Socket\Pool($dbCon);

$socket->on('connection', function(\React\Socket\ConnectionInterface $connection) use ($pool){
    $pool->add($connection);
});


echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";

$loop->run();

