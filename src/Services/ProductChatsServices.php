<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Models\UserModel;
use App\Database;

final class ProductChatsServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAll(): PromiseInterface{
        $query = "SELECT product_chats.* FROM product_chats ORDER BY `id` DESC ";

        return $this->db->query($query)->then(function (QueryResult $queryResult) {
            return $queryResult->resultRows;
        },function ($er){
            throw new \Exception($er);
        });;
    }

    public function findLatestForTwoUsers($user_id, $second_user_id): PromiseInterface{
        $query  =   "SELECT * FROM product_chats WHERE 
                        (receiver_id = '$user_id' AND sender_id = '$second_user_id') OR 
                        (receiver_id = '$second_user_id' AND sender_id = '$user_id') 
                    ORDER BY `id` DESC LIMIT 1 ";

            return $this->db->query($query)->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
            },function ($er){
                throw new \Exception($er);
            });
    }

    private function findById($id): PromiseInterface{
        $query = "SELECT product_chats.* FROM product_chats WHERE `id` = $id  ";
        return $this->db->query($query)->then(function (QueryResult $result) {
            if (empty($result->resultRows)) {
                return [];
            }
            return $result->resultRows[0];
        },function ($er){
            throw new \Exception($er);
        });;
    }


    public function update(\App\Models\ProductChatsModel $productChatsModel): PromiseInterface{
        if(empty($productChatsModel->product_id)){
            return (new \App\Utils\PromiseResponse())::rejectPromise("Product ID not found");
        }
        return $this->findById($productChatsModel->id)
            ->then(function ($oldData) use ($productChatsModel) {
                $query  = "UPDATE product_chats SET 
                        `product_id` = ? , 
                        `offer_product_id` = ? , 
                        sender_id = ?  , 
                        receiver_id = ?  , 
                        chat_status = ? 

                        WHERE id = ? ";

                return $this->db->query($query, [
                    $productChatsModel->product_id          ??  $oldData['product_id'], 
                    $productChatsModel->offer_product_id    ??  $oldData['offer_product_id'],
                    $productChatsModel->sender_id           ??  $oldData['sender_id'],
                    $productChatsModel->receiver_id         ??  $oldData['receiver_id'],
                    $productChatsModel->chat_status         ??  $oldData['chat_status'],

                    $productChatsModel->id
                ])->then(function () use ($productChatsModel){
                    return $this->findById($productChatsModel->id);
                },
                function (\Exception $error) {
                    return "Error: $error";
                });
            },
            function (\Exception $error) {
                return "Nothing no found. Error: $error";
            });
    }


    public function create(\App\Models\ProductChatsModel $productChatsModel): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($productChatsModel->product_id)){
            return $promiseResponse::rejectPromise("No product ID found");
        }

        //--> Check if I have already established connection with this user's product
        return $this->db->query(
            "SELECT * FROM product_chats WHERE product_id = ? AND sender_id = ? ",
        [
            $productChatsModel->product_id,
            $productChatsModel->sender_id,
        ]
        )
        ->then(function (QueryResult $result) use ($productChatsModel){
            if (empty($result->resultRows)) {
                //--> INSERT
                $insertQuery  = "INSERT INTO `product_chats` (`id`, `product_id`, `offer_product_id`, `sender_id`, `receiver_id`) 
                VALUES (?, ?, ?, ?, ?)";
                return $this->db->query($insertQuery,[
                    NULL, 
                    $productChatsModel->product_id, 
                    $productChatsModel->offer_product_id, 
                    $productChatsModel->sender_id, 
                    $productChatsModel->receiver_id
                ])->then(function () {
                    return $this->findById('LAST_INSERT_ID()');
                },
                function (\Exception $error) {
                    return "Error: $error";
                });
            }else{
                $productChatsModel->id = $result->resultRows[0]["id"];
                //UPDATE
                return $this->update($productChatsModel);
            }
        });;
        
    }
}


                    