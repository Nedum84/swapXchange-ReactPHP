<?php

namespace App\Services;

use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\AgoraToken\RtcTokenBuilder;

final class AgoraCallToken{
    private static $appID = "18f66d8a4cf141c29daf271a39cf8fe2";
    private static $appCertificate = "21ad1ef8942e4ae3b2d5957ab162e1dd";
    private static $role = RtcTokenBuilder::RoleAttendee;
    private static $expireTimeInSeconds = 600;//10 mins

    public function __construct(){}

    public function generateAgoraToken($user_id, $uid, $channelName): PromiseInterface{
        // $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
        // $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        $privilegeExpiredTs = time() + self::$expireTimeInSeconds;

        if (empty($uid)||empty($channelName)) {
            return  \App\Utils\PromiseResponse::resolvePromise("UID & Channel name are required");
        }
        

        $token = RtcTokenBuilder::buildTokenWithUid(
            self::$appID, 
            self::$appCertificate, 
            $channelName, 
            $uid, 
            self::$role, 
            $privilegeExpiredTs
        );
        return \App\Utils\PromiseResponse::rejectPromise([$token]);
    }

}


                    