<?php
namespace App\Controller\User;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\UserServices;


final class UpdateUserAddress{
    private $userServices;

    public function __construct(Database $db){
        $this->userServices = new UserServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        $user = new \App\Models\UserModel();
        $user->address          = $body['address'] ?? ''; 
        $user->address_lat      = $body['address_lat'] ?? ''; 
        $user->address_long     = $body['address_long'] ?? ''; 
        $user->state            = $body['state'] ?? ''; 

            return $this->userServices->updateAddress($user, $user_id)
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
