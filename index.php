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
use App\Utils\JsonRequestDecoder;


require __DIR__ . '/vendor/autoload.php';

$user = "ned";
$pass = "ned";
$db_name = "swapxchange";
$host = "localhost";
$host = "192.168.64.2";


// $user = "swapx_change";
// $pass = "Nellyson23#$";
// $db_name = "swap_x_change";
// $host = "localhost:3306";

// $url = "$user:$pass@$host/$db";
$url = rawurlencode($user) . ':' . rawurlencode($pass) . "@".$host."/".$db_name;




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
$productAuth = new Guard('/v1/products', $authenticator);
$catAuth = new Guard('/v1/category', $authenticator);
$subCatAuth = new Guard('/v1/subcategory', $authenticator);
$prodctChatAuth = new Guard('/v1/productchats', $authenticator);
$imgUploadAuth = new Guard('/v1/image', $authenticator);//Uploading Images
$uploadAuth = new Guard('/uploads', $authenticator);//View Files(Or Images)
$userAuth1 = new Guard('/v1/users/me', $authenticator);
$userAuth2 = new Guard('/v1/users/address', $authenticator);
$userAuth3 = new Guard('/v1/users/user', $authenticator);//--> /users/{user_id}
$coinsAuth = new Guard('/v1/coins', $authenticator);
$faqsAuth = new Guard('/v1/faqs', $authenticator);
$feedbackAuth = new Guard('/v1/feedback', $authenticator);
$reportedproductsAuth = new Guard('/v1/reportedproducts', $authenticator);

// Add routes to the server
$server = new React\Http\Server(
    $loop, 
    // new JsonRequestDecoder(),
    // new \React\Http\Middleware\RequestBodyBufferMiddleware(20 * 1024 * 1024), // 20 MiB per request
    // new \React\Http\Middleware\StreamingRequestMiddleware(),//To use stream data chunk
    // new \React\Http\Middleware\RequestBodyParserMiddleware(),
    $productAuth, 
    $catAuth, 
    $subCatAuth, 
    $prodctChatAuth, 
    // $imgUploadAuth,
    $uploadAuth,
    $userAuth1,
    $userAuth2,
    $userAuth3,
    $coinsAuth,
    $faqsAuth,
    $feedbackAuth,
    $reportedproductsAuth,
    new \App\Router($routes)
);


//open the socket
$socket = new \React\Socket\Server(8088, $loop);
// $socket = new \React\Socket\Server("199.192.27.225:8088", $loop);

$server->listen($socket);

$server->on('error', function (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
});

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";



$loop->run();

