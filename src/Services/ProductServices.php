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
    public const RADIUS = 2522;
    public const ACTIVE_PRODUCT_STATUS = 1;

    //Adding number of products on category/subcategory row results
    public static function noOfProductQuery($user_lat, $user_long, $extra = ""){
        $product_status = \App\Services\ProductServices::ACTIVE_PRODUCT_STATUS;
        $radius = \App\Services\ProductServices::RADIUS;
        return "SELECT 
                    COUNT(
                        (((acos(sin(('$user_lat'*pi()/180)) * 
                        sin((`user_address_lat`*pi()/180))+cos(('$user_lat'*pi()/180))
                        *  cos((`user_address_lat`*pi()/180)) * 
                        cos((('$user_long'- `user_address_long`)*pi()/180))))*180/pi())*60*1.1515)
                    ) AS distance

                from product 
                WHERE product_status = '$product_status' $extra
                having distance < '$radius'
        ";
    }

    private function selectQuery(
                        $limit, 
                        $offset, 
                        $user_lat, 
                        $user_long, 
                        $product_status = self::ACTIVE_PRODUCT_STATUS, 
                        $extra="", 
                        $orderBy=null){
        $radius = self::RADIUS;
        $orderBy = $orderBy??"ORDER BY id DESC";

        return "SELECT product.*, 
                (((acos(sin(('$user_lat'*pi()/180)) * 
                sin((`user_address_lat`*pi()/180))+cos(('$user_lat'*pi()/180))
                * 
                cos((`user_address_lat`*pi()/180)) * 
                cos((('$user_long'- `user_address_long`)*pi()/180))))*180/pi())*60*1.1515)
                AS distance
                ,JSON_EXTRACT(
                        IFNULL(
                            (SELECT
                                CONCAT('[',
                                        GROUP_CONCAT(
                                            JSON_OBJECT(
                                                'id',id,
                                                'product_id',product_id,
                                                'image_id',image_id,
                                                'image_url',image_url
                                            )
                                        ),
                                ']')
                                FROM image_product WHERE  image_product.product_id = product.product_id
                            ),
                        '[]'),
                    '$') AS images


            from product 
            WHERE product_status = '$product_status' $extra
            having distance < '$radius' 
                $orderBy
                LIMIT $limit OFFSET $offset 
            ";
    }

    public function __construct(Database $database){
        $this->db = $database->db;
        $this->database = $database;
    }

    public function findAll($user_id, $offset, $limit): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($offset, $limit){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $product_status = self::ACTIVE_PRODUCT_STATUS;

            $query = $this->selectQuery($limit, $offset, $user_lat, $user_long, $product_status);

            return $this->db->query($query)
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            },function ($er){
                throw new \Exception($er);
            });
        });
    }

    public function findByCategory($user_id, $category, $offset, $limit): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($offset, $limit, $category){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $product_status = self::ACTIVE_PRODUCT_STATUS;

            $extra = "AND category = $category";
            $query = $this->selectQuery($limit, $offset, $user_lat, $user_long, $product_status, $extra);

            return $this->db->query($query)
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            },function ($er){
                throw new \Exception($er);
            });
        });
    }
    public function findBySubCategory($user_id, $subcategory, $offset, $limit): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($offset, $limit, $subcategory){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $product_status = self::ACTIVE_PRODUCT_STATUS;

            $extra = "AND sub_category = $subcategory";
            $query = $this->selectQuery($limit, $offset, $user_lat, $user_long, $product_status, $extra);

            return $this->db->query($query)
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            },function ($er){
                throw new \Exception($er);
            });
        });
    }
    public function findBySearch($user_id, $searchQuery, $filters, $offset, $limit): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($offset, $limit, $searchQuery, $filters){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $product_status = self::ACTIVE_PRODUCT_STATUS;

            $extra = "AND product_name LIKE '%$searchQuery%' 
                        OR category IN 
                            (SELECT category_id FROM category WHERE category_name LIKE '%$searchQuery%' ) 
                        OR sub_category IN 
                            (SELECT sub_category_id FROM sub_category WHERE sub_category_name LIKE '%$searchQuery%' ) 
                    ";

            $orderBy = null;
            if(!empty($filters)){
                switch ($filters) {
                    case 'best-match':
                        $orderBy = "ORDER BY id DESC";
                        break;
                    case 'price-high':
                        $orderBy = "ORDER BY price DESC";
                        break;
                    case 'price-low':
                        $orderBy = "ORDER BY price ASC";
                        break;
                    case 'newest':
                        $orderBy = "ORDER BY id DESC";
                        break;
                    case 'oldest':
                        $orderBy = "ORDER BY id ASC";
                        break;
                                
                    default:
                        $orderBy = "ORDER BY id DESC";
                        break;
                }
            }
            $query = $this->selectQuery($limit, $offset, $user_lat, $user_long, $product_status, $extra, $orderBy);

            return $this->db->query($query)
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            }, function ($er){
                throw new \Exception($er);
            });
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
                `product_suggestion`, `product_condition`, `product_status`, `user_id`, `user_address`, 
                `user_address_city`, `user_address_lat`, `user_address_long`) 
            VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

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
                $product->user_id, 
                $product->user_address, 
                $product->user_address_city, 
                $product->user_address_lat, 
                $product->user_address_long
            ]);

    }
}


                    