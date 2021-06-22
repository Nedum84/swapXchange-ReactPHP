<?php

namespace App\Controller\User;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\UserServices;
use App\Services\TokenServices;
use \App\Services\CoinsServices;


final class CreateUser{
    private $userServices;
    private $tokenServices;
    private $coinsServices;

    public function __construct(Database $db){
        $this->userServices = new UserServices($db);
        $this->tokenServices = new TokenServices($db);
        $this->coinsServices = new CoinsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        // $body = json_decode((string) $request->getBody(), true);
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve,&$requestBody) {
                $body               = json_decode($requestBody, true);
                $user = new \App\Models\UserModel();
                $user->uid        = $body['uid'] ?? ''; 
                $user->name      = $body['name'] ?? ''; 
                $user->email          = $body['email'] ?? ''; 
                $user->mobile_number      = $body['mobile_number'] ?? ''; 
                $user->address             = $body['address'] ?? ''; 
                $user->address_lat      = $body['address_lat'] ?? ''; 
                $user->address_long     = $body['address_long'] ?? ''; 
                $user->state            = $body['state'] ?? '';  
                $user->profile_photo    = $body['profile_photo'] ?? ''; 
                $user->device_token     = $body['device_token'] ?? ''; 
                $user->online_status    = $body['online_status']; 
                $user->user_app_version = $body['user_app_version'] ?? ''; 
                $user->last_login       = date("Y-m-d H:i:s",\time()); 

                $resolve(
                    //Check if the uuid(uid) is already registered
                    $this->userServices->findByUid($user->uid)
                        ->then(function(array $oldUser) use ($user) {
                            //If already registered, return the user
                            if(count($oldUser)!=0&&!empty($oldUser)){ 
                                //Update user
                                if(empty($oldUser['name'])){
                                    $update = $this->userServices->update($user, $oldUser["user_id"]);
                                }else{
                                    $update = $this->userServices->updateLastLogin($user, $oldUser["user_id"]);
                                }
                                return $update->then(function($user) {
                                        if(gettype($user)!=="array"){
                                            return JsonResponse::badRequest($user);
                                        };
                                        //Fetch token...
                                        return $this->tokenServices->generateToken($user['user_id'],$user['uid'])->then(
                                            function(array $tokens) use ($user){
                                            return JsonResponse::ok(["user" => $user,"tokens"=>$tokens]);
                                        }); 
                                    },
                                    function (\Exception $error) {
                                        return JsonResponse::badRequest($error);
                                });
                            }

                            //Register a new user
                            return $this->userServices->create($user)
                                ->then(function($user) {
                                    if(gettype($user)!=="array"){
                                        return JsonResponse::badRequest($user);
                                    };

                                    //Add Registration Coins Bonus
                                    $amount = 500;
                                    $methodOfSub = "registration";
                                    return $this->coinsServices->create($user['user_id'], $amount, "", $methodOfSub)
                                    ->then(function ($balance) use ($user) {
                                        //Fetch token...
                                        return $this->tokenServices->generateToken($user['user_id'],$user['uid'])->then(
                                            function(array $tokens) use ($user){
                                            return JsonResponse::ok(["user" => $user,"tokens"=>$tokens]);
                                        }); 
                                    });
                                },
                                function (\Exception $error) {
                                    return JsonResponse::badRequest($error->getMessage());
                                });
                        })
                );
            });
        });



    }
}
