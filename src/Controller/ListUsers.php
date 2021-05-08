<?php

namespace App\Controller;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

final class ListUsers
{
    private $users;

    public function __construct(Database $users)
    {
        $this->users = $users;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        return new Response(405, ['Content-Type' => 'text/plain'], 'Method not 9090909090');
        try {
            return $this->users->all()
                ->then(function(array $users) {
                    return JsonResponse::ok($users);
                });
          } catch (\Throwable $e) {
            // return JsonResponse::badRequest($er);
            return new Response(405, ['Content-Type' => 'text/plain'], 'Method not allowedxxsss'.$e);
          }
        // return $this->users->all()
        //     ->then(function(array $users) {
        //         return JsonResponse::ok($users);
        //     });
    }
}
