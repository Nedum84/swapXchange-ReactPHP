<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Models\UserModel;
use App\Database;

final class CategoryServices{
    private $db;
    private $database;

    public function __construct(Database $database){
        $this->db = $database->db;
        $this->database = $database;
    }

    private function getNumberOfProduct($categories, $products){
        $result = [];
        foreach ($categories as $category) {
            foreach ($products as $product) {
                if ($category["category_id"] == $product["category"]) {
                    $category["no_of_products"] = $product["no_of_products"];
                } else {
                    if (empty($category["no_of_products"])) {
                        $category["no_of_products"] = "0";
                    }
                }
            }

            if (empty($subcategory["no_of_products"])) {
                $subcategory["no_of_products"] = "0";
            }
            $result[] = $category;
        }
        return $result;
    }

    public function findAll($user_id): PromiseInterface{
        $groupBy = "GROUP BY category";
        $productServices = new \App\Services\ProductServices($this->database);
        return $productServices->noOfProductQuery($user_id, $groupBy)->then(function ($products) {
            
            $query = "SELECT category.* FROM category ORDER BY `idx` ";
            return $this->db->query($query)->then(function (QueryResult $queryResult) use ($products) {
                $categories =  $queryResult->resultRows;
                $result = $this->getNumberOfProduct($categories, $products);

                return $result;
            },function ($er){
                throw new \Exception($er);
            });
        },function ($er){
            throw new \Exception($er);
        });
    }

    public function findOne($id, $user_id): PromiseInterface{
        $groupBy = "GROUP BY category";
        $productServices = new \App\Services\ProductServices($this->database);
        return $productServices->noOfProductQuery($user_id, $groupBy)->then(function ($products) use ($id) {
            
            $query = "SELECT category.* FROM category WHERE `category_id` = $id  ";
            return $this->db->query($query)->then(function (QueryResult $queryResult) use ($products) {
                if (empty($queryResult->resultRows)) {
                    return [];
                }

                $categories =  $queryResult->resultRows;
                $result = $this->getNumberOfProduct($categories, $products);

                return $result;
            },function ($er){
                throw new \Exception($er);
            });
        },function ($er){
            throw new \Exception($er);
        });
    }


    public function update(string $category_id , string $category_name, $category_icon, $idx, $user_id): PromiseInterface{

        if(empty($category_name)){
            return (new \App\Utils\PromiseResponse())::rejectPromise("Enter category name");
        }else if(empty($category_icon)){
            return (new \App\Utils\PromiseResponse())::rejectPromise("No category icon found");
        }else if(empty($category_id)){
            return (new \App\Utils\PromiseResponse())::rejectPromise("No category ID found");
        }
        return $this->findOne($category_id, $user_id)
            ->then(function ($oldCategory) use ($category_id,$category_name, $category_icon, $idx, $user_id) {
                $query  = "UPDATE category SET 
                        `category_name` = ? , 
                        `category_icon` = ? , 
                        idx = ? 
                        WHERE category_id = ? ";

                return $this->db->query($query, [
                    $category_name, 
                    $category_icon,
                    $idx??$oldCategory['idx'],

                    $category_id
                ])->then(function () use ($category_id, $user_id){
                    return $this->findOne($category_id, $user_id);
                },
                function (\Exception $error) {
                    return "Error: $error";
                });
            },
            function (\Exception $error) {
                return "Category no found. Error: $error";
            });
    }


    public function create(string $category_name, string $category_icon, $user_id): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($category_name)){
            return $promiseResponse::rejectPromise("Enter category name");
        }else if(empty($category_icon)){
            return $promiseResponse::rejectPromise("No category icon found");
        }
        
        $query  = "INSERT INTO `category` (`category_id`, `category_name`, `category_icon`, `idx`) 
                VALUES (NULL, '$category_name', '$category_icon', LAST_INSERT_ID()+1)";

        return $this->db->query($query)->then(function () use ($user_id) {
            return $this->findOne('LAST_INSERT_ID()', $user_id);
        },
        function (\Exception $error) {
            return "Error: $error";
        });
    }
}


                    