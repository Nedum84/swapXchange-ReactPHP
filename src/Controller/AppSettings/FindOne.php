<?php

namespace App\Controller\AppSettings;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\AppSettingsServices;

final class FindOne{
    private $appSeetingsServices;

    public function __construct(Database $db){
        $this->appSeetingsServices = new AppSettingsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $key){

        return $this->appSeetingsServices->find($key)
            ->then(function(array $response) use ($key) {
                if(\count($response)==0)
                    return JsonResponse::badRequest("Nothing found");
                return JsonResponse::ok(["$key" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
