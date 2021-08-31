<?php
namespace App\Controller\User;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\UserServices;


final class UpdateUser{
    private $userServices;

    public function __construct(Database $db){
        $this->userServices = new UserServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;

        $user = new \App\Models\UserModel();
        $user->uid        = $body['uid'] ?? ''; 
        $user->name      = $body['name'] ?? ''; 
        $user->email          = $body['email'] ?? ''; 
        $user->mobile_number    = $body['mobile_number'] ?? ''; 
        $user->profile_photo    = $body['profile_photo'] ?? ''; 
        $user->device_token     = $body['device_token'] ?? ''; 
        $user->user_app_version = $body['user_app_version'] ?? ''; 
        $user->notification     = \json_encode($body['notification'] ?? (object)$user->defaultNotification); 
        $user->last_login       = date("Y-m-d H:i:s",\time()) ?? $body['last_login']; 


            return $this->userServices->update($user, $user_id)
            ->then(function ($user) {
                    if(gettype($user)!=="array"){
                        return JsonResponse::badRequest($user);
                    };
                    //Include user in the response data payload
                    return JsonResponse::ok(["user" => $user]);
                },
                function (\Exception $error) {
                    return JsonResponse::badRequest($error->getMessage());
                }
            );
    }
}
