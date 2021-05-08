<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Models\ProductModel;
use App\Database;

final class ProductServices{
    private $db;
    private $database;

    public function __construct(Database $database){
        $this->db = $database->db;
        $this->database = $database;
    }

    public function findAll($user_id, $offset, $limit): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($offset, $limit){

            $query = "SELECT * FROM product ORDER BY id DESC LIMIT ? OFFSET ?";
            return $this->db->query($query,[$limit, $offset])
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            });
        });
    }

    public function findOne(string $id): PromiseInterface{
        return $this->db->query('SELECT * FROM product WHERE id = ?', [$id])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }

                return $result->resultRows[0];
            });
    }
    public function findByProductId(string $pId): PromiseInterface{
        return $this->db->query('SELECT * FROM product WHERE product_id = ?', [$pId])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
            });
    }
    public function delete(string $id): PromiseInterface{
        return $this->db
            ->query('DELETE FROM product WHERE id = ?', [$id])
            ->then(
                function (QueryResult $result) {
                    if ($result->affectedRows === 0) {
                        throw new UserNotFoundError();
                    }
                });
    }


    public function update(ProductModel $product , string $product_id): PromiseInterface{
        return $this->findByProductId($product_id)
            ->then(function () use ($product, $product_id) {
                $query  = "UPDATE product SET 
                        product_name = ? , 
                        category = ? , 
                        sub_category = ? , 
                        price = ? , 
                        product_description = ? , 
                        product_suggestion = ? , 
                        product_condition = ? , 
                        product_status = ? , 
                        user_address = ? , 
                        user_address_city = ? , 
                        user_address_lat = ? , 
                        user_address_long = ?
                        WHERE product_id = ? ";

                $this->db->query($query, [
                    $product->product_name, 
                    $product->category,
                    $product->sub_category, 
                    $product->price, 
                    $product->product_description, 
                    $product->product_suggestion, 
                    $product->product_condition, 
                    $product->product_status, 
                    $product->user_address, 
                    $product->user_address_city, 
                    $product->user_address_lat, 
                    $product->user_address_long,

                    $product_id
                ]);
            });
    }


    public function create(ProductModel $product): PromiseInterface {
        $query = 'INSERT INTO 
            `product` 
                (`id`, `product_id`, `product_name`, `category`, `sub_category`, `price`, `product_description`, 
                `product_suggestion`, `product_condition`, `product_status`, `timestamp`, `user_id`, `user_address`, 
                `user_address_city`, `user_address_lat`, `user_address_long`) 
            VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        return $this->db->query($query, [
                NULL, 
                $product->product_id, 
                $product->product_name, 
                $product->category, 
                $product->sub_category, 
                $product->price, 
                $product->product_description, 
                $product->product_suggestion, 
                $product->product_condition, 
                $product->product_status, 
                $product->timestamp, 
                $product->user_id, 
                $product->user_address, 
                $product->user_address_city, 
                $product->user_address_lat, 
                $product->user_address_long
            ]);

    }
}


                    