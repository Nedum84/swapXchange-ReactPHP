<?php

namespace App\Controller\Token;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\TokenServices;


final class RefreshToken{
    private $tokenServices;

    public function __construct(Database $db){
        $this->tokenServices = new TokenServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $refresh_token    = $body['refresh_token']; 

            //Fetch token...
            return $this->tokenServices->refreshToken($refresh_token)->then(
                function( $tokens) {
                    if(empty($tokens)||count($tokens)==0)
                        return JsonResponse::unauthorized('Invalid Refresh Token');
                return JsonResponse::ok(["tokens" => $tokens]);
            },
            function (\Exception $error) {
                return JsonResponse::badRequest($error->getMessage());
            });
    }
}
