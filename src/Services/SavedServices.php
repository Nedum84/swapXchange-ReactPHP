<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;

final class SavedServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAll($id, $offset=0, $limit=10): PromiseInterface{
        $imgSubQuery = \App\Services\ProductServices::imgSubQuery();
        $userSubQuery = \App\Services\ProductServices::userSubQuery();

        $query = "SELECT  product.*, 
                    $imgSubQuery, 
                    $userSubQuery 
                FROM product  
                WHERE product.product_id IN 
                    (SELECT saved_products.product_id FROM saved_products 
                        WHERE saved_products.product_id = product.product_id
                        AND saved_products.user_id = '$id'
                    ) 
                ORDER BY product_id DESC 
                        LIMIT $limit OFFSET $offset 
                ";

            return $this->db->query($query)
                ->then(function (QueryResult $result) {

                    return $result->resultRows;
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }

    public function remove(int $user_id, int $product_id): PromiseInterface{
        return $this->db
            ->query('DELETE FROM saved_products WHERE user_id = ? AND product_id = ? ', [$user_id, $product_id])
            ->then(
                function (QueryResult $result) {
                    if ($result->affectedRows === 0) {
                        return "Product/User ID not found";
                    }
                    return "Product unsaved";
                });
    }

    public function findOne(int $user_id, int $product_id): PromiseInterface{
        return $this->db->query("SELECT * FROM saved_products WHERE user_id = ? AND product_id = ? ",[$user_id, $product_id])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
        });
    }

    public function create(int $user_id, int $product_id): PromiseInterface {
        return $this->findOne($user_id, $product_id)->then(function ($result) use ($user_id, $product_id){
            if (\count($result)!=0) {

                $query = 'DELETE FROM saved_products WHERE user_id = ? AND product_id = ? ';
                return $this->db
                    ->query($query, [$user_id, $product_id])
                    ->then(
                        function (QueryResult $result) {
                            if ($result->affectedRows === 0) {
                                return "Product/User ID not found";
                            }
                            return "Product unsaved";
                        });
            }else{
                $query = "INSERT INTO `saved_products` (`id`, `product_id`, `user_id`) VALUES (?, ?, ?)";

                return $this->db->query($query, [
                        NULL, 
                        $product_id, 
                        $user_id
                    ])->then(function () {
                        return "Product saved!";
                    },
                    function (\Exception $error) {
                        return "Error: $error";
                    });
            }
        });
        
    }
}


                    