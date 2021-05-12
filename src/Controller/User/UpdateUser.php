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
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
        
        $body = json_decode((string) $request->getBody(), true);

        $user = new \App\Models\UserModel();
        $user->uid        = $body['uid'] ?? ''; 
        $user->name      = $body['name'] ?? ''; 
        $user->email          = $body['email'] ?? ''; 
        $user->mobile_number      = $body['mobile_number'] ?? ''; 
        $user->profile_photo    = $body['profile_photo'] ?? ''; 
        $user->device_token         = $body['device_token'] ?? ''; 
        $user->user_app_version           = $body['user_app_version'] ?? ''; 
        $user->last_login      = time()?? $body['last_login'] ?? '0'; 

        return $this->userServices->update($user, $user_id)
            ->then(
                function () use ($user_id) {
                    //Include user in the response data payload
                    return $this->userServices->findOne($user_id)->then(
                        function(array $user) {
                        return JsonResponse::ok(["user" => $user]);
                    });
                },
                function (Exception $error) {
                    return JsonResponse::badRequest($error->getMessage());
                }
            );
    }
}
