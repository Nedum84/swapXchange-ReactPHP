<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;

final class ProductViewsServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAll($product_id): PromiseInterface{
        $query = "SELECT  product_views.* FROM product_views  
                    WHERE product_views.product_id = $product_id ";

            return $this->db->query($query)
                ->then(function (QueryResult $result) {

                    return $result->resultRows;
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }


    public function findOne(int $user_id, int $product_id): PromiseInterface{
        return $this->db->query("SELECT * FROM product_views WHERE user_id = ? AND product_id = ? ",[$user_id, $product_id])
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
                return $result;
            }else{
                if (empty($user_id)) {
                    return [];
                }
                $query = "INSERT INTO `product_views` (`view_id`, `product_id`, `user_id`) VALUES (?, ?, ?)";

                return $this->db->query($query, [
                        NULL, 
                        $product_id, 
                        $user_id
                    ])->then(function () use ($user_id, $product_id) {
                        return $this->findOne($user_id, $product_id);
                    },
                    function (\Exception $error) {
                        return "Error: $error";
                    });
            }
        });
        
    }
}


                    