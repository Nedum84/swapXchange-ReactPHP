<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Models\UserModel;
use App\Database;

final class CoinsServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAllByUserId($user_id): PromiseInterface{
        //Get Balance
        return $this->getBalance($user_id)
            ->then(function (array $balance) use ($user_id) {
                //Get my data
                return $this->db->query('SELECT * FROM coins WHERE `user_id` = ? OR reference = ? ORDER BY id DESC LIMIT 500 ', [$user_id, $user_id])
                ->then(function (QueryResult $result) use ($balance) {
                    $balance["meta"] = $result->resultRows;
                    return $balance;
                });
            });
    }

    public function getBalance($user_id): PromiseInterface{
        //Get added coins
        return $this->db->query('SELECT SUM(amount) AS total_coins FROM coins WHERE `user_id` = ? ', [$user_id])
            ->then(function (QueryResult $result) use ($user_id) {

                $total_coins =  (int) $result->resultRows[0]['total_coins'];
                //Get uploaded products
                return $this->db->query('SELECT SUM(upload_price) AS total_upload_amount FROM product WHERE `user_id` = ? ', [$user_id])
                ->then(function (QueryResult $result) use ($total_coins, $user_id) {
    
                    $total_upload_amount = (int) $result->resultRows[0]["total_upload_amount"];
                    //Get my transfers
                    return $this->db->query('SELECT SUM(amount) AS total_transfers FROM coins WHERE `reference` = ? ', [$user_id])
                    ->then(function (QueryResult $result) use ($total_coins, $total_upload_amount, $user_id) {
                        
                        $total_transfers = (int) $result->resultRows[0]["total_transfers"];
        
                        //Get my last transaction data
                        return $this->db->query('SELECT * FROM coins WHERE `user_id` = ? ORDER BY id DESC LIMIT 1 ', [$user_id])
                        ->then(function (QueryResult $result) use ($total_coins, $total_upload_amount, $total_transfers, $user_id) {

                            $balance = $total_coins - $total_upload_amount - $total_transfers;
                            $last_credit = $result->resultRows[0];
                            $last_credit["current_time"] = \date("Y-m-d H:i:s",\time());
                            if (empty($result->resultRows[0])) {
                                $last_credit["id"] = "";
                                $last_credit["user_id"] = "0";
                                $last_credit["amount"] = "0";
                                $last_credit["reference"] = "0";
                                $last_credit["method_of_subscription"] = "registration";
                                $last_credit["created_at"] = \date("Y-m-d H:i:s",\time());
                            }
                            
                            
                            return array(
                                'total_coins'       =>$total_coins,
                                'total_upload_amount'  =>$total_upload_amount,
                                'total_transfers'   =>$total_transfers,
                                'balance'       =>$balance,
                                'last_credit'   =>$last_credit
                            );
                        });
                        
                    });
                    
                });
            });
    }

    //Check if already gotten registration
    public function checkRegistrationBonus($meta, $user_id){
        foreach ($meta as $transaction) {
            $mos = $transaction['method_of_subscription'];
            if ($transaction["user_id"]==$user_id && $mos == "registration") {
                return true;
                break;
            }
        }

        return false;
    }

    //Check already gotten daily rewards
    public function checkDailyReward($meta, $user_id){
        foreach ($meta as $transaction) {
            //same day
            if ($transaction["user_id"]==$user_id) {
                $created_at = $transaction['created_at'];
                $created_at = \date('Y-m-d', \strtotime($created_at));
                $now = \date('Y-m-d', \time());

                if($created_at==$now){
                    return true;
                    break;
                }
            }
        }

        return false;
    }

    //Check if already gotten registration
    public function verifyReference($reference){
        //The parameter after verify/ is the transaction reference to be verified
        $url = "https://api.paystack.co/transaction/verify/$reference";
        $sk_key = "sk_test_9cd3e56e95f4abea6a56643e45ca3f606210effc";
        // $sk_key = "sk_live_1627b9cf52161b293eafcd2a5d3a9b68a0a2d726";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $sk_key"]
        );
        $request = curl_exec($ch);
        curl_close($ch);

        if ($request) {
            $result = json_decode($request, true);
            
            if($result['data']){
                if($result['data']['status'] == 'success'){
                    return true;
                }
            }
        }

        return false;
    }


    public function create(int $user_id, int $amount, $reference, $method_of_subscription): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($amount)){
            return $promiseResponse::rejectPromise("Enter amount");
        }else if(empty($method_of_subscription)){
            return $promiseResponse::rejectPromise("No method of payment detected");
        }

        //Check my duplicate trasaction ref
        return $this->db->query('SELECT * FROM coins WHERE reference = ? ', [$reference])
        ->then(function (QueryResult $queryResult) use ($user_id, $amount, $reference, $method_of_subscription) {
            if (!empty($queryResult->resultRows)) {
                return "Duplicate transaction ref not allowed";
            }
            //--> Continue...
            return $this->findAllByUserId($user_id)
            ->then(function ($balance) use ($user_id, $amount, $reference, $method_of_subscription) {

                //Methods of subs(registration, purchase, watch_video, daily_opening, invitation, transfer, coupon)
                $methodOfSubs = ["registration", "purchase", "watch_video", "daily_opening", "invitation", "transfer", "coupon"];
                $allowedMethods = ["registration", "purchase", "daily_opening"];
                if (!in_array($method_of_subscription, $methodOfSubs)) {
                    return "Invalid method of subscription";
                }elseif (!in_array($method_of_subscription, $allowedMethods)) {
                    return "Method of subscription not allowed currently";
                }elseif(empty($balance)){
                    return "User authentication failed";
                }elseif ($method_of_subscription=="registration") {
                    //Check if user has alerady gotten this reg bonus
                    $checkRegBonus = $this->checkRegistrationBonus($balance["meta"], $user_id);
                    if($checkRegBonus){
                        return "User already claimed registration bonus";
                    }
                }else if ($method_of_subscription=="purchase") {
                    //paystack check for reference valid
                    $verify = $this->verifyReference($reference);
                    if (!$verify) {
                        return "Invalid reference";
                    }
                }else if ($method_of_subscription=="daily_opening") {
                    //Check if already gotten today's reward
                    $checkDaily = $this->checkDailyReward($balance["meta"], $user_id);
                    if ($checkDaily) {
                        return "You can't receive daily coins twice per daily. try again tomorrow";
                    }
                }
                $last_credit = $balance["last_credit"];
                $last_amount = $last_credit['amount'];
                $last_reference = $last_credit['reference'];
                $last_mos = $last_credit['method_of_subscription'];
                $last_created_at = $last_credit['created_at'];
                // $last_current_time = $last_credit['current_time'];

                if ($last_mos=="registration" && $method_of_subscription=="registration") {
                    return "Registration bonus can be received once";
                } 
                if ($method_of_subscription=="registration" && $amount!=500) {
                    return "Invalid amount";
                } 
                if ($method_of_subscription=="daily_opening" && $amount!=10) {
                    return "Invalid amount";
                } 
                if ($method_of_subscription=="purchase") {
                    $purchaseAmounts = [500, 1000, 5000];
                    if(!\in_array($amount, $purchaseAmounts)){
                        return "Invalid amount for this reference::1";
                    }
                    //XPCOHN6CDN_5000_31_2300-> rand_no, coin_amount, user_id, purchased_amount
                    $purchaseAmounts = \explode("_",$reference);
                    if(\count($purchaseAmounts)<3){
                        return "Invalid reference number";
                    }
                    $coinsAmount = intval($purchaseAmounts[1]);
                    if($coinsAmount!=$amount){
                        return "Invalid amount for this reference::2";
                    }
                } 
        
                $query  = "INSERT INTO `coins` (`id`, `user_id`, `amount`, `reference`, `method_of_subscription`) 
                    VALUES (?, ?, ?, ?, ?)";
        
                return $this->db->query($query, 
                    [
                        NULL,
                        $user_id,
                        $amount,
                        $reference,
                        $method_of_subscription
                    ])->then(function () use ($user_id) {
                    return $this->getBalance($user_id);
                },
                function (\Exception $error) {
                    return "Error: $error";
                });
            },
            function (\Exception $error) {
                return "Error: $error";
            });

        });
    }
}


                    