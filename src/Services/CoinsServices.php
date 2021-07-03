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



    public function create(int $user_id, int $amount, $reference, $method_of_subscription): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($amount)){
            return $promiseResponse::rejectPromise("Enter amount");
        }else if(empty($method_of_subscription)){
            return $promiseResponse::rejectPromise("No method of payment detected");
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
    }
}


                    