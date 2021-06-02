<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;
use App\JWTAuth\JwtEncoder;
use React\Promise\Deferred;

final class TokenServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    private function toTimeEpoch($time){
        return date("Y-m-d H:i:s",$time);
    }


    public function generateToken(string $user_id, string $uid = null):PromiseInterface{

        // $accessExpires = strtotime("+ 30 days");
        $accessExpires = strtotime("+ 20 minutes");
        $refreshExpires = strtotime("next month");
        $accessPayload = array(
            "iss" => "swapxchange.shop",
            "aud" => "swapxchange.shop",
            "iat" => time(),// the timestamp of token issuing.
            // "nbf" => $accessExpires,//a timestamp of when the token should start being considered valid. Should be equal to or greater than iat
            "exp" => $accessExpires,//a timestamp of when the token should cease to be valid. Should be greater than iat and nbf
            "data"=>array(
                    "user_id"=>$user_id, 
                    "uid"=>$uid
                )
        );
        $refreshPayload = array(
            "iss" => "swapxchange.shop",
            "aud" => "swapxchange.shop",
            "iat" => time(),
            // "nbf" => $refreshExpires,
            "exp" => $refreshExpires,
            "data"=>array(
                "user_id"=>$user_id, 
                "uid"=>$uid
            )
        );
        $jwt = new JwtEncoder();
        $access_token = $jwt->encode($accessPayload);
        $refresh_token = $jwt->encode($refreshPayload);

        // convert to time in Y-m-d H:i:s 
        $accessExpires = $this->toTimeEpoch($accessExpires);
        $refreshExpires = $this->toTimeEpoch($refreshExpires);
        return $this->db->query(
                "INSERT INTO `token`
                    (`user_id`, token, expires)
                VALUES($user_id, '$refresh_token', '$refreshExpires')
                    ON DUPLICATE KEY UPDATE
                        `token` = '$refresh_token', expires = '$refreshExpires'"
            )->then(function () use ($access_token, $accessExpires, $refresh_token, $refreshExpires){
                //Return token array
                return array(
                        "access"=>array(
                            "token"=>$access_token,
                            "expires"=>$accessExpires,
                        ),
                        "refresh"=>array(
                            "token"=>$refresh_token,
                            "expires"=>$refreshExpires,
                        ),
                );
        },
        function (Exception $error) {
            return "Error: $error";
        });
    }


    public function refreshToken(string $refresh_token):PromiseInterface {
        $response = new \App\Utils\PromiseResponse();

        $jwt = new JwtEncoder();

        try {
            $refresh    = $jwt->decode($refresh_token);
        }catch(\UnexpectedValueException $er){
            return $response::rejectPromise([]);
        }catch(\Firebase\JWT\ExpiredException $er){
            return $response::rejectPromise([]);
        }catch(\DomainException $er){
            return $response::rejectPromise([]);
        }
        
        $user_id    = $refresh["data"]->user_id;
        $uid        = $refresh["data"]->uid;
        if(empty($user_id)||empty($uid))
            return $response::rejectPromise();

        return $this->findByToken($refresh_token, $user_id)
            ->then(function (array $dbToken) use ($uid, $user_id) {
                if(count($dbToken)==0){
                    return [];
                }

                return $this->generateToken($user_id, $uid);
            });
    }


    public function findByToken(string $token, string $user_id): PromiseInterface{
        return $this->db->query('SELECT * FROM token WHERE `token` = ? AND `user_id` = ? ', [$token, $user_id])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }

                return $result->resultRows[0];
            });
    }

}


                    