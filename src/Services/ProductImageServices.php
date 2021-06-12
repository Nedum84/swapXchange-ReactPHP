<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;
use \App\Models\ServiceResponse;

final class ProductImageServices{
    private $db;
    private $database;

    public function __construct(Database $database){
        $this->db = $database->db;
        $this->database = $database;
    }


    public function findAllByProductId($productId): PromiseInterface{
        if(empty($productId)){
            return \App\Utils\PromiseResponse::rejectPromise(new ServiceResponse(false, "No product ID found"));
        }
        $query = "SELECT image_product.* FROM image_product WHERE `product_id` = ?  ";
        return $this->db->query($query,[$productId])->then(function (QueryResult $queryResult) {
            return new ServiceResponse(true, $queryResult->resultRows);
        },function ($er){
            throw new \Exception($er);
        });
    }

    public function findOne($id): PromiseInterface{
        $query = "SELECT * FROM image_product WHERE `id` = $id  ";

        return $this->db->query($query)->then(function (QueryResult $result) {
            if (empty($result->resultRows)) {
                return new ServiceResponse(true, []);
            }
            return new ServiceResponse(true, $result->resultRows[0]);
        },function ($er){
            return new ServiceResponse(false, $er);
        });;
    }


    public function delete(string $id, string $projectRoot, \React\Filesystem\Filesystem $filesystem): PromiseInterface{
        $imageUploadServices = new \App\Services\ImageUploadServices($projectRoot ,$filesystem);

        return $this->findOne($id)
        ->then(function ($oldData) use ($id, $imageUploadServices) {
            $oldData = $oldData->data;
            $oldImagePath = $oldData['image_path']??"";

            return $this->db->query('DELETE FROM image_product WHERE id = ?', [$id])
            ->then(function (QueryResult $result) use ($oldImagePath, $imageUploadServices) {
                    return $imageUploadServices->delete($oldImagePath)->then(function ($var){
                        return new ServiceResponse(true, "Image deleted successfully");
                    });
                });
        },
        function (\Exception $error) {
            return new ServiceResponse(false, "Product image ID not found. Error: $error");
        });
    }

    public function update(?int $id, ?string $product_id, ?string $image_path, $idx): PromiseInterface{
        $promiseResponse = new \App\Utils\PromiseResponse();

        if(empty($id)){
            return $promiseResponse::rejectPromise(new ServiceResponse(false, "No ID found"));
        }

        return $this->findOne($id)
            ->then(function ($oldData) use ($id, $product_id,$image_path, $idx) {
                $query  = "UPDATE image_product SET 
                        `product_id` = ? , 
                        `image_path` = ? , 
                        `idx` = ? 
                        WHERE id = ? ";
                        
                $oldData = $oldData->data;

                return $this->db->query($query, [
                    $product_id ?? $oldData['product_id'], 
                    $image_path ?? $oldData['image_path'],
                    $idx        ?? $oldData['idx'],

                    $id
                ])->then(function () use ($id){
                    return $this->findOne($id);
                },
                function (\Exception $error) {
                    return new ServiceResponse(false, "Error: $error");
                });
            },
            function (\Exception $error) {
                return new ServiceResponse(false, "Product image ID not found. Error: $error");
        });
    }


    public function create(?string $product_id, ?string $image_path, $idx, $pImgId=0): PromiseInterface {
        $promiseResponse = new \App\Utils\PromiseResponse();
        if(empty($product_id)){
            return $promiseResponse::rejectPromise(new ServiceResponse(false, "No product ID found"));
        }else if(empty($image_path)){
            return $promiseResponse::rejectPromise(new ServiceResponse(false, "Image upload path not found"));
        }

        //Check if already added and update else insert
        return $this->findOne($pImgId)
            ->then(function ($oldData) use ($pImgId, $product_id,$image_path, $idx) {
                if(empty($oldData->data)){
                    $query  = "INSERT INTO `image_product` (`id`, `product_id`, `image_path`, `idx`) 
                            VALUES (?, ?, ?, ?)";
    
                    return $this->db->query($query,[
                        NULL,
                        $product_id,
                        $image_path,
                        $idx,
                    ])->then(function () use ($user_id) {
                        return $this->findOne('LAST_INSERT_ID()');
                    },
                    function (\Exception $error) {
                        return new ServiceResponse(false, "Error: $error");
                    });
                }
                else{
                    return $this->update($pImgId, $product_id, $image_path, $idx);
                }
            },
            function (\Exception $error) {
                return new ServiceResponse(false, "Product image ID not found. Error: $error");
        });
    }
}


                    