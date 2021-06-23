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
    public const UNPUBLISHED_PRODUCT_STATUS     = 1;
    public const PENDING_APPROVAL_PRODUCT_STATUS= 2;
    public const ACTIVE_PRODUCT_STATUS          = 3;
    public const COMPLETED_PRODUCT_STATUS       = 4;
    public const DELETED_PRODUCT_STATUS         = 5;
    public const BLOCKED_PRODUCT_STATUS         = 6;

    public static function imgSubQuery(){
        return "JSON_EXTRACT(
                IFNULL(
                    (SELECT
                        CONCAT('[',
                                GROUP_CONCAT(
                                    JSON_OBJECT(
                                        'id',id,
                                        'product_id',product_id,
                                        -- 'image_id',image_id,
                                        'image_path',image_path,
                                        'idx',idx
                                    )
                                ),
                        ']')
                        FROM image_product WHERE  image_product.product_id = product.product_id 
                        ORDER BY CAST(image_product.idx AS UNSIGNED )
                    ),
                '[]'),
            '$') AS images";
    }
    public static function userSubQuery(){
        return "JSON_EXTRACT(
                    IFNULL(
                        (SELECT
                        JSON_OBJECT
                            (
                                'user_id',user_id,
                                'name',name,
                                'mobile_number',mobile_number,
                                'address',address,
                                'profile_photo',profile_photo
                            )
                        FROM users WHERE  users.user_id = product.user_id
                        ),
                    '{}'),
                '$') AS user

                -- product suggestions categories array
                ,JSON_EXTRACT(
                    IFNULL(
                        (SELECT
                            CONCAT('[',
                                    GROUP_CONCAT(
                                        JSON_OBJECT(
                                            'category_id',category_id,
                                            'category_name',category_name,
                                            'category_icon',category_icon
                                        )
                                    ),
                            ']')
                            FROM category WHERE category.category_id IN 
                                (
                                    SELECT category_id FROM category WHERE  
                                        category_id = SUBSTRING_INDEX(product.product_suggestion, ',',1) -- First one
                                        OR category_id = SUBSTRING_INDEX(product.product_suggestion, ',',-1) -- Last one
                                        OR category_id = SUBSTRING_INDEX(SUBSTRING_INDEX(product.product_suggestion, ',',2), ',',-1) -- Second one
                                        OR category_id = SUBSTRING_INDEX(SUBSTRING_INDEX(product.product_suggestion, ',',3), ',',-1) -- Third one
                                        OR category_id = SUBSTRING_INDEX(SUBSTRING_INDEX(product.product_suggestion, ',',4), ',',-1) -- 4th
                                        OR category_id = SUBSTRING_INDEX(SUBSTRING_INDEX(product.product_suggestion, ',',5), ',',-1) -- 5th
                                        OR category_id = SUBSTRING_INDEX(SUBSTRING_INDEX(product.product_suggestion, ',',6), ',',-1) -- 6th
                                        --category_id = SUBSTRING_INDEX(product.product_suggestion, ',',-1) 
                                        --category_id = SUBSTRING_INDEX(product.product_suggestion, ',',2)  
                                )
                        ),
                    '[]'),
                '$') AS suggestions
                
                
                , (
                    SELECT COUNT(*) FROM product_views  WHERE product_views.product_id = product.product_id              
                ) as no_of_views
            ";
    }

    //Adding number of products on category/subcategory row results
    public function noOfProductQuery($user_id, $groupBy = "GROUP BY category"): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($groupBy){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            // $user_lat = 36.6103179;
            // $user_long = -121.8449227;
            $product_status = self::ACTIVE_PRODUCT_STATUS;
            $radius = self::RADIUS;

            $query ="SELECT 
                        category,
                        sub_category,
                        COUNT(product.product_id) as no_of_products
                        ,(((acos(sin(('$user_lat'*pi()/180)) * 
                        sin((`user_address_lat`*pi()/180))+cos(('$user_lat'*pi()/180))
                        * 
                        cos((`user_address_lat`*pi()/180)) * 
                        cos((('$user_long'- `user_address_long`)*pi()/180))))*180/pi())*60*1.1515)
                        AS distance

                    from product 
                    WHERE product_status = '$product_status'
                    $groupBy 
                    having distance < '$radius'
                    ";

            return $this->db->query($query)
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            },function ($er){
                throw new \Exception($er);
            });
        });
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
        $orderBy = $orderBy??"ORDER BY product_id DESC";
        $imgSubQuery = self::imgSubQuery();
        $userSubQuery = self::userSubQuery();

        return "SELECT product.*, 
                (((acos(sin(('$user_lat'*pi()/180)) * 
                sin((`user_address_lat`*pi()/180))+cos(('$user_lat'*pi()/180))
                * 
                cos((`user_address_lat`*pi()/180)) * 
                cos((('$user_long'- `user_address_long`)*pi()/180))))*180/pi())*60*1.1515)
                AS distance
                ,$imgSubQuery
                ,$userSubQuery
                , (
                    SELECT COUNT(*) FROM product_views  WHERE product_views.product_id = product.product_id              
                ) as no_of_views



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
    public function findBySubCategory($user_id, $subcategory, $offset, $limit, $filters): PromiseInterface{
        $userServices = new \App\Services\UserServices($this->database);
        
        return $userServices->findOne($user_id)->then(function ($user) use ($offset, $limit, $subcategory, $filters){

            $user = (object)$user;
            $user_lat = $user->address_lat;
            $user_long = $user->address_long;
            $product_status = self::ACTIVE_PRODUCT_STATUS;

            $extra = "AND sub_category = $subcategory";

            $orderBy = null;
            if(!empty($filters)){
                switch ($filters) {
                    case 'best-match':
                        $orderBy = "ORDER BY product_id DESC";
                        break;
                    case 'price-high':
                        $orderBy = "ORDER BY price DESC";
                        break;
                    case 'price-low':
                        $orderBy = "ORDER BY price ASC";
                        break;
                    case 'newest':
                        $orderBy = "ORDER BY product_id DESC";
                        break;
                    case 'oldest':
                        $orderBy = "ORDER BY product_id ASC";
                        break;
                                
                    default:
                        $orderBy = "ORDER BY product_id DESC";
                        break;
                }
            }
            $query = $this->selectQuery($limit, $offset, $user_lat, $user_long, $product_status, $extra, $orderBy);

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
            $searchQuery = ($searchQuery!="none")?$searchQuery:"";

            $extra = "AND product_name LIKE '%$searchQuery%' 
                        OR category IN 
                            (SELECT category_id FROM category WHERE category_name LIKE '%$searchQuery%' ) 
                        OR sub_category IN 
                            (SELECT sub_category_id FROM subcategory WHERE sub_category_name LIKE '%$searchQuery%' ) 
                    ";

            $orderBy = null;
            if(!empty($filters)){
                switch ($filters) {
                    case 'best-match':
                        $orderBy = "ORDER BY product_id DESC";
                        break;
                    case 'price-high':
                        $orderBy = "ORDER BY price DESC";
                        break;
                    case 'price-low':
                        $orderBy = "ORDER BY price ASC";
                        break;
                    case 'newest':
                        $orderBy = "ORDER BY product_id DESC";
                        break;
                    case 'oldest':
                        $orderBy = "ORDER BY product_id ASC";
                        break;
                                
                    default:
                        $orderBy = "ORDER BY product_id DESC";
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


    public function findExchangeOptions($product_id, $user_id, $offset, $limit): PromiseInterface{
        return $this->findOne($product_id)
            ->then(function ($product) use ($user_id, $product_id, $offset, $limit) {
                if(empty($product))return [];

                $userServices = new \App\Services\UserServices($this->database);
                return $userServices->findOne($user_id)->then(function ($user) use ($product, $offset, $limit, $product_id){
        
                    $user               = (object)$user;
                    $user_lat           = $user->address_lat;
                    $user_long          = $user->address_long;
                    $product_status     = self::ACTIVE_PRODUCT_STATUS;
                    $product_price      = $product['price'];
                    $product_interests  = $product['product_suggestion'];
        
                    $extra = "AND product_id != $product_id";//complex later with ($product_price & $product_interests)
                    $query = $this->selectQuery($limit, $offset, $user_lat, $user_long, $product_status, $extra);
        
                    return $this->db->query($query)
                    ->then(function (QueryResult $queryResult) {
                        return $queryResult->resultRows;
                    },function ($er){
                        throw new \Exception($er);
                    });
                });
            });
    }
    
    public function findOne($pId): PromiseInterface{
        $imgSubQuery = self::imgSubQuery();
        $userSubQuery = self::userSubQuery();
        return $this->db->query("SELECT product.*, $imgSubQuery, $userSubQuery FROM product  WHERE product_id = $pId ")
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
        });
    }

    public function findMyProducts($user_id, $offset, $limit): PromiseInterface{
        $imgSubQuery = self::imgSubQuery();
        $userSubQuery = self::userSubQuery();
        $query = "SELECT product.*, $imgSubQuery , $userSubQuery
                    FROM product 
                        WHERE user_id = $user_id 
                            ORDER BY product_id DESC 
                                LIMIT $limit OFFSET $offset ";

        return $this->db->query($query)
        ->then(function (QueryResult $queryResult) {
            return $queryResult->resultRows;
        },function ($er){
            throw new \Exception($er);
        });
    }


    //--> User not me
    public function findUserProducts($user_id, $filter, $offset, $limit): PromiseInterface{
        if(empty($user_id)){
            return (new \App\Utils\PromiseResponse())::rejectPromise([]);
        }
        $active = self::ACTIVE_PRODUCT_STATUS;
        $imgSubQuery = self::imgSubQuery();
        $userSubQuery = self::userSubQuery();
        $extra = ($filter=="all")?"":"AND product_status = $filter";

        $query = "SELECT product.*, $imgSubQuery, $userSubQuery FROM product 
                    WHERE user_id = $user_id 
                            $extra
                            ORDER BY product_id DESC 
                                LIMIT $limit OFFSET $offset ";

        return $this->db->query($query)
        ->then(function (QueryResult $queryResult) {
            return $queryResult->resultRows;
        },function ($er){
            throw new \Exception($er);
        });
    }
    //--> Get Searched suggestions
    public function findSuggestions($searchQuery, $user_id): PromiseInterface{
        $query ="SELECT DISTINCT product_name as item FROM product WHERE product_name LIKE '%$searchQuery%'
                    UNION
                SELECT DISTINCT category_name as item FROM category WHERE category_name LIKE '%$searchQuery%'
                    UNION
                SELECT DISTINCT sub_category_name as item FROM subcategory WHERE sub_category_name LIKE '%$searchQuery%'
                ";
        return $this->db->query($query)
            ->then(function (QueryResult $result) {
                return $result->resultRows;
        });
    }

    //--> Find Nearby Usersf for a product ID
    public function findNearbyUsers($product_lat, $product_long): PromiseInterface{
        $radius = self::RADIUS;
        $query = "SELECT `user_id`, `device_token`, `notification`, `name`, 
                (((acos(sin(('$product_lat'*pi()/180)) * 
                sin((`address_lat`*pi()/180))+cos(('$product_lat'*pi()/180))
                * 
                cos((`address_lat`*pi()/180)) * 
                cos((('$product_long'- `address_long`)*pi()/180))))*180/pi())*60*1.1515)
                AS distance

            from users 
            having distance < '$radius' 
            ORDER BY distance
            LIMIT 100
        ";

        return $this->db->query($query)
        ->then(function (QueryResult $queryResult) {
            return $queryResult->resultRows;
        },function ($er){
            throw new \Exception($er);
        });
    }

    public function update(ProductModel $product , int $product_id): PromiseInterface{
        return $this->findOne($product_id, $product->user_id)
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

                return $this->db->query($query, [ 
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
                ])->then(function () use ($product) {
                    return $this->findOne($product->product_id, $product->user_id);
                },
                function (\Exception $error) {
                    return "Error: $error";
                });
            });
    }


    public function create(ProductModel $product): PromiseInterface {
        $query = 'INSERT INTO 
            `product` 
                (`product_id`, `order_id`, `product_name`, `category`, `sub_category`, `price`, `product_description`, 
                `product_suggestion`, `product_condition`, `product_status`, `user_id`, `user_address`, 
                `user_address_city`, `user_address_lat`, `user_address_long`, `upload_price`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        return $this->db->query($query, [
                NULL, 
                $product-> order_id, 
                $product-> product_name, 
                $product-> category, 
                $product-> sub_category, 
                $product-> price, 
                $product-> product_description, 
                $product-> product_suggestion, 
                $product-> product_condition, 
                $product-> product_status, 
                $product-> user_id, 
                $product-> user_address, 
                $product-> user_address_city, 
                $product-> user_address_lat, 
                $product-> user_address_long, 
                $product-> upload_price
            ])->then(function () use ($product) {
                return $this->findOne('LAST_INSERT_ID()', $product->user_id);
            },
            function (\Exception $error) {
                return "Error: $error";
            });

    }
}


                    