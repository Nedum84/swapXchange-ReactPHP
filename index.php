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
$authenticator = new JwtAuthenticator(new JwtEncoder());


$routes = new RouteCollector(new Std(), new GroupCountBased());

$routes->addGroup('/v1', function (RouteCollector $v1Routes) use ($dbCon, $routes) {
    //Route to v1
    new \App\Routes\v1\RoutesIndex($v1Routes, $dbCon);
    // new \App\Routes\v1\RoutesIndex($routes, $dbCon);
});

// Add jwt auth middleware... 
$auth = new Guard('/v1/products', $authenticator);
$auth2 = new Guard('/v1/userz', $authenticator);


// Add routes to the server
$server = new React\Http\Server($loop, $auth, new \App\Router($routes));

//open the socket
$socket = new \React\Socket\Server(8088, $loop);

$server->listen($socket);

$server->on('error', function (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";


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


// $colours = ['red', 'green', 'yellow', 'blue', 'purple', 'cyan'];
// // This event triggers every time a new connection comes in
// $socket->on('connection', function ($conn) use ($colours) {
//     $colour = array_pop($colours); // Only doing this as an example, you will run out of colours.

//     // Event listener for incoming data
//     $conn->on('data', function ($data, $conn) use ($colour) {
//         // Write data back to the connection
//         $conn->write($data);

//         // Echo the data into our terminal window
//         echo (new \Malenki\Ansi($data))->fg($colour);
//     });
// });

// Listen on port 1337
// $socket->listen(1337);

$loop->run();

