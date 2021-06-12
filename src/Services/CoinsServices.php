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
            ->then(function (int $balance) {
                //Get my data
                return $this->db->query('SELECT * FROM coins WHERE `user_id` = ? OR reference = ? ', [$user_id, $user_id])
                ->then(function (QueryResult $result) use ($balance) {
                    return [
                        "balance" => $balance,
                        "meta" =>$result->resultRows,
                    ];
                });
            });
    }

    public function getBalance($user_id): PromiseInterface{
        //Get added coins
        return $this->db->query('SELECT SUM(amount) AS total_coins FROM coins WHERE `user_id` = ? ', [$user_id])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }

                \var_dump($result);
                return $result->resultRows[0];
                //Get uploaded products
                return $this->db->query('SELECT SUM(upload_price) AS total_upload_amount FROM product WHERE `user_id` = ? ', [$user_id])
                ->then(function (QueryResult $result) {
                    if (empty($result->resultRows)) {
                        return [];
                    }
    
                    \var_dump($result);
                    return $result->resultRows[0];
                    //Get my transfers
                    return $this->db->query('SELECT SUM(amount) AS total_transfers FROM coins WHERE `reference` = ? ', [$user_id])
                    ->then(function (QueryResult $result) {
                        if (empty($result->resultRows)) {
                            return [];
                        }
        
                        \var_dump($result);
                        return $result->resultRows[0];
        
                        
                    });
                    
                });
            });
    }



    public function create(int $user_id, int $amount, $reference, $method_of_subscription): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($amount)){
            return $promiseResponse::rejectPromise("Enter amount name");
        }else if(empty($method_of_subscription)){
            return $promiseResponse::rejectPromise("No method of payment detected");
        }
        
        $query  = "INSERT INTO `coins` (`id`, `user_id`, `amount`, `reference`, `method_of_subscription`) 
            VALUES (?, ?, ?, ?, ?, ?)";

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


                    