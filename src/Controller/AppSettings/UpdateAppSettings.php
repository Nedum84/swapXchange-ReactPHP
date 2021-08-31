<?php
namespace App\Controller\AppSettings;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\AppSettingsServices;


final class UpdateAppSettings{
    private $appSeetingsServices;

    public function __construct(Database $db){
        $this->appSeetingsServices = new AppSettingsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $key        = $body['key'] ?? ''; 
        $value        = $body['value'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->appSeetingsServices->update($key, $value, $user_id) 
            ->then(function ($response) use ($key) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::ok(["$key" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
