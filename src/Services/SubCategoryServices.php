<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Models\UserModel;
use App\Database;


final class SubCategoryServices{
    private $db;
    private $productServices;
    private $database;

    public function __construct(Database $database){
        $this->db = $database->db;
        $this->database = $database;
    }

    private function getNumberOfProduct($subcategories, $products){
        $result = [];
        foreach ($subcategories as $subcategory) {
            foreach ($products as $product) {
                if ($subcategory["sub_category_id"] == $product["sub_category"]) {
                    $subcategory["no_of_products"] = $product["no_of_products"];
                } else {
                    if (empty($subcategory["no_of_products"])) {
                        $subcategory["no_of_products"] = "0";
                    }
                }
            }

            if (empty($subcategory["no_of_products"])) {
                $subcategory["no_of_products"] = "0";
            }
            $result[] = $subcategory;
        }
        return $result;
    }

    public function findAll($user_id): PromiseInterface{
        $groupBy = "GROUP BY sub_category";
        $productServices = new \App\Services\ProductServices($this->database);
        return $productServices->noOfProductQuery($user_id, $groupBy)->then(function ($products) {
            
            $query = "SELECT subcategory.* FROM subcategory ORDER BY `idx` ";
            return $this->db->query($query)->then(function (QueryResult $queryResult) use ($products) {
                $subcategories =  $queryResult->resultRows;
                $result = $this->getNumberOfProduct($subcategories, $products);

                return $result;
            },function ($er){
                throw new \Exception($er);
            });
        },function ($er){
            throw new \Exception($er);
        });
    }
    public function findByCategoryId($user_id, $category_id): PromiseInterface{
        $groupBy = "GROUP BY sub_category";
        $productServices = new \App\Services\ProductServices($this->database);
        return $productServices->noOfProductQuery($user_id, $groupBy)->then(function ($products) use ($category_id) {
            
            $query = "SELECT subcategory.* FROM subcategory 
                    WHERE subcategory.category_id = '$category_id'
                    ORDER BY `idx` ";

            return $this->db->query($query)->then(function (QueryResult $queryResult) use ($products) {
                $subcategories =  $queryResult->resultRows;
                $result = $this->getNumberOfProduct($subcategories, $products);

                return $result;
            },function ($er){
                throw new \Exception($er);
            });
        },function ($er){
            throw new \Exception($er);
        });
    }

    public function findAll2($user_id): PromiseInterface{
        return $this->db->query("SELECT subcategory.*
                                ,(SELECT COUNT(DISTINCT product.id) FROM product 
                                    WHERE product.sub_category = subcategory.sub_category_id
                                ) as no_of_products

                                FROM subcategory
                                ORDER BY `idx` ")
            ->then(function (QueryResult $queryResult) use ($user_id) {
                $rows = $queryResult->resultRows;
                return $rows;
            });
    }

    public function findOne($id, $user_id): PromiseInterface{
        $groupBy = "GROUP BY sub_category";
        $productServices = new \App\Services\ProductServices($this->database);
        return $productServices->noOfProductQuery($user_id, $groupBy)->then(function ($products) use ($id) {
            
            $query = "SELECT subcategory.* FROM subcategory WHERE `sub_category_id` = $id  ";
            return $this->db->query($query)->then(function (QueryResult $queryResult) use ($products) {
                if (empty($queryResult->resultRows)) {
                    return [];
                }

                $subcategories =  $queryResult->resultRows;
                $result = $this->getNumberOfProduct($subcategories, $products);

                return $result;
            },function ($er){
                throw new \Exception($er);
            });
        },function ($er){
            throw new \Exception($er);
        });
    }


    public function update(
                string $category_id, 
                string $sub_category_id, 
                string $sub_category_name, 
                $sub_category_icon, 
                $idx,
                $user_id): PromiseInterface{

        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($sub_category_name)){
            return $promiseResponse::rejectPromise("Enter sub category name");
        }else if(empty($sub_category_icon)){
            return $promiseResponse::rejectPromise("No icon found");
        }else if(empty($category_id)||empty($sub_category_id)){
            return $promiseResponse::rejectPromise("No category or sub-catgeory ID found");
        }

        return $this->findOne($sub_category_id, $user_id)
            ->then(function ($oldSubCategory) use ($category_id, $sub_category_id,$sub_category_name, $sub_category_icon, $idx,$user_id) {
                $query  = "UPDATE subcategory SET 
                        `sub_category_name` = ? , 
                        `sub_category_icon` = ? , 
                        `category_id` = ? , 
                        idx = ? 
                        WHERE sub_category_id = ? ";

                return $this->db->query($query, [
                    $sub_category_name, 
                    $sub_category_icon,
                    $category_id,
                    $idx??$oldSubCategory['idx'],

                    $sub_category_id
                ])->then(function () use ($sub_category_id, $user_id){
                    return $this->findOne($sub_category_id, $user_id);
                },
                function (\Exception $error) {
                    return "Error: $error";
                });
            },
            function (\Exception $error) {
                return "Category no found. Error: $error";
            });
    }


    public function create(string $category_id, string $sub_category_name, string $sub_category_icon, $user_id): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($sub_category_name)){
            return $promiseResponse::rejectPromise("Enter sub category name");
        }else if(empty($sub_category_icon)){
            return $promiseResponse::rejectPromise("No icon found");
        }else if(empty($category_id)){
            return $promiseResponse::rejectPromise("No category ID found");
        }

        $query  = "INSERT INTO `subcategory` (`sub_category_id`, `sub_category_name`, `sub_category_icon`, `category_id`, `idx`) 
                VALUES (NULL, '$sub_category_name', '$sub_category_icon', '$category_id', LAST_INSERT_ID()+1)";

        return $this->db->query($query)->then(function () use ($user_id){
            return $this->findOne('LAST_INSERT_ID()', $user_id);
        },
        function (\Exception $error) {
            return "Error: $error";
        });
    }
}


                    