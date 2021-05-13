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

$loop       = \React\EventLoop\Factory::create();
$factory    = new Factory($loop);
$filesystem = \React\Filesystem\Filesystem::create($loop);
$db         = $factory->createLazyConnection($url);
$dbCon      = new \App\Database($db);
$authenticator = new JwtAuthenticator(new JwtEncoder());


$routes = new RouteCollector(new Std(), new GroupCountBased());

$routes->addGroup('/v1', function (RouteCollector $v1Routes) use ($dbCon, $routes, $filesystem) {
    //Route to v1
    new \App\Routes\v1\RoutesIndex($v1Routes, $dbCon, $filesystem, __DIR__);
    // new \App\Routes\v1\RoutesIndex($routes, $dbCon);
});
// files... 
$routes->get('/uploads/{file:.*\.\w+}', new \App\Controller\StaticFiles\StaticFilesController($filesystem, __DIR__));

// Add jwt auth middleware... 
$auth = new Guard('/v1/products', $authenticator);
$catAuth = new Guard('/v1/category', $authenticator);
$subCatAuth = new Guard('/v1/subcategory', $authenticator);
$chatCatAuth = new Guard('/v1/productchats', $authenticator);
$imgCatAuth = new Guard('/v1/image', $authenticator);
$imgCatAuth = new Guard('/v1/image', $authenticator);

// Add routes to the server
$server = new React\Http\Server($loop, $auth, $catAuth, $subCatAuth, $chatCatAuth, $imgCatAuth, new \App\Router($routes));

//open the socket
$socket = new \React\Socket\Server(8088, $loop);

$server->listen($socket);

$server->on('error', function (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";



$loop->run();

