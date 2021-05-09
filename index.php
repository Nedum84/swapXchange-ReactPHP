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


// $user = "provxyzx_pgmi_user";
// $pass = 'uWA*~$;${M4a';
// $db = "provxyzx_provinceofgrace_org_datas";

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
$authenticator = new JwtAuthenticator(new JwtEncoder());


$routes = new RouteCollector(new Std(), new GroupCountBased());

$routes->addGroup('/v1', function (RouteCollector $v1Routes) use ($dbCon, $routes) {
    //Products
    $routes->addGroup('/products', function (RouteCollector $r) use ($dbCon, $v1Routes) {
        new \App\Routes\ProductRoutes($r, $dbCon);
    });
});
// //Products
// $routes->addGroup('/products', function (RouteCollector $r) use ($dbCon) {
//     new \App\Routes\ProductRoutes($r, $dbCon);
// });
//Users
$routes->addGroup('/users', function (RouteCollector $r) use ($dbCon) {
    new \App\Routes\UserRoutes($r, $dbCon);
});
//Users
$routes->addGroup('/token', function (RouteCollector $r) use ($dbCon) {
    new \App\Routes\TokenRoutes($r, $dbCon);
});

// Add jwt auth middleware... 
$auth = new Guard('/products', $authenticator);
$auth2 = new Guard('/userz', $authenticator);


// $server = new React\Http\Server($loop, $auth, $auth2, new \App\Router($routes));
$server = new React\Http\Server($loop, $auth, new \App\Router($routes));

$socket = new \React\Socket\Server(8088, $loop);

$server->listen($socket);

$server->on('error', function (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";
// for socket connections... 
// $socket->on('connection', function (React\Socket\ConnectionInterface $connection) {
//     $connection->write("Hello " . $connection->getRemoteAddress() . "!\n");
//     $connection->write("Welcome to this amazing server!\n");
//     $connection->write("Here's a tip: don't say anything.\n");
//     $connection->write("Here's a tip: don't say anything 2.\n");
//     $connection->write("Here's a tip: don't say anything 3.\n");

//     $connection->on('data', function ($data) use ($connection) {
//         $connection->close();
//     });
// });

$payload = array(
    "iss" => "example.org",
    "aud" => "example.com",
    "iat" => 1356999524,
    "nbf" => 1357000000
);
$jwt =  new JwtEncoder();
$access_token = $jwt->encode($payload);
$refresh_token = $jwt->decode($access_token);

// var_dump($refresh_token);
// echo strtotime("2014-01-01 00:00:01");

$loop->run();

