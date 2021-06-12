<?php

declare(strict_types=1);

namespace App\Utils;

use Psr\Http\Message\ServerRequestInterface;
use \React\Http\Middleware\StreamingRequestMiddleware;

final class JsonRequestDecoder{
    public function __invoke(ServerRequestInterface $request, callable $next){
        if ($request->getHeaderLine('Content-type') === 'application/json') {
            $request = $request->withParsedBody(
                json_decode($request->getBody()->getContents(), true)
            );
        }


        // var_dump($request->getHeader('authorization')[0]);
        return $next($request);
    }
}
