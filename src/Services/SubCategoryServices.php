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

    public function findAll($user_id): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        return $userServices->findOne($user_id)->then(function ($user) {

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $extra = "AND product.sub_category = subcategory.sub_category_id";

            $no_of_product_query = \App\Services\ProductServices::noOfProductQuery($user_lat, $user_long, $extra);
            $query = "SELECT subcategory.*,
                        -- no of products for this sub categories
                            ($no_of_product_query) as no_of_products

                        FROM subcategory
                        ORDER BY `idx` ";

            return $this->db->query($query)->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            },function ($er){
                throw new \Exception($er);
            });
        },function ($er){
            throw new \Exception($er);
        });
    }
    public function findByCategoryId($user_id, $category_id): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        return $userServices->findOne($user_id)->then(function ($user) use ($category_id){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $extra = "AND product.sub_category = subcategory.sub_category_id";

            $no_of_product_query = \App\Services\ProductServices::noOfProductQuery($user_lat, $user_long, $extra);
            $query = "SELECT subcategory.*,
                        -- no of products for this sub categories
                            ($no_of_product_query) as no_of_products

                        FROM subcategory
                        WHERE subcategory.category_id = '$category_id'
                        ORDER BY `idx` ";

            return $this->db->query($query)->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
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
        $userServices = new \App\Services\UserServices($this->database);
        return $userServices->findOne($user_id)->then(function ($user) use ($id) {
            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $extra = "AND product.sub_category = subcategory.sub_category_id";

            $no_of_product_query = \App\Services\ProductServices::noOfProductQuery($user_lat, $user_long, $extra);
            $query = "SELECT subcategory.*,
                        -- no of products for this sub categories
                            ($no_of_product_query) as no_of_products

                        FROM subcategory
                        WHERE `sub_category_id` = $id  ";

            return $this->db->query($query)->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
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


                    