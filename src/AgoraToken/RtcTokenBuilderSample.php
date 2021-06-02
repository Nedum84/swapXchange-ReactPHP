<?php
// namespace App\AgoraToken;
include("RtcTokenBuilder.php");

$appID = "18f66d8a4cf141c29daf271a39cf8fe2";
$appCertificate = "21ad1ef8942e4ae3b2d5957ab162e1dd";
$channelName = "you";
$uid = 1232;
$uidStr = "2882341273";
$role = RtcTokenBuilder::RoleAttendee;
$expireTimeInSeconds = 3600*2000;
$currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
$privilegeExpiredTs = time() + $expireTimeInSeconds;

$token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
echo 'Token with int uid: ' . $token . PHP_EOL;

$token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $uidStr, $role, $privilegeExpiredTs);
echo 'Token with user account: ' . $token . PHP_EOL;

$token = RtcTokenBuilder::buildTokenWithUidAndPrivilege($appID, $appCertificate, $channelName, $uid,
                                                                $privilegeExpiredTs, $privilegeExpiredTs,
                                                                $privilegeExpiredTs, $privilegeExpiredTs);
echo 'Token with user uid user defined privilege: ' . $token . PHP_EOL;

$token = RtcTokenBuilder::buildTokenWithUserAccountAndPrivilege($appID, $appCertificate, $channelName, $uidStr,
                                                                    $privilegeExpiredTs, $privilegeExpiredTs,
                                                                    $privilegeExpiredTs, $privilegeExpiredTs);
echo 'Token with user account user defined privilege: ' . $token . PHP_EOL;



?>