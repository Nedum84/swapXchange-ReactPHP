<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;


final class CreateProduct
{
    private $mysql;

    public function __construct(Database $mysql)
    {
        $this->mysql = $mysql;
    }

    public function __invoke(ServerRequestInterface $request){
        try {
            return $this->all()
                ->then(function(array $mysql) {
                    return JsonResponse::ok($mysql);
                });
          } catch (\Throwable $e) {
            // return JsonResponse::badRequest($er);
            return new Response(405, ['Content-Type' => 'text/plain'], 'Method not allowed'.$e);
          }

        return new Response(405, ['Content-Type' => 'text/plain'], 'Method not allowedxxzzzz');
        try {
            return $this->users->all()
                ->then(function(array $mysql) {
                    return JsonResponse::ok($mysql);
                });
          } catch (\Throwable $e) {
            // return JsonResponse::badRequest($er);
            return new Response(405, ['Content-Type' => 'text/plain'], 'Method not allowedxxsss'.$e);
          }
        // return $this->users->all()
        //     ->then(function(array $mysql) {
        //         return JsonResponse::ok($mysql);
        //     });
    }


    public function all(): PromiseInterface
    {
            return $this->mysql->db->query('SELECT * FROM users')
                ->then(function (QueryResult $queryResult) {
                    return $queryResult->resultRows;
                });
    }
}
