<?php

namespace App\Controller\Agora;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\AgoraCallToken;



final class GenerateCallToken{
    private $agoraTokenServices;

    public function __construct(Database $db){
        $this->agoraTokenServices = new AgoraCallToken();
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $uid            = $body['uid'] ?? '';
        $channel_name   = $body['channel_name'] ?? '';
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->agoraTokenServices->generateAgoraToken($user_id, $uid, $channel_name) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::ok(["token" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
