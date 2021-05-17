<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Models\UserModel;
use App\Database;

final class UserServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAll(): PromiseInterface{
        return $this->db->query('SELECT * FROM users')
            ->then(function (QueryResult $queryResult) {
                return $queryResult->resultRows;
            });
    }

    public function findOne($id): PromiseInterface{
        return $this->db->query('SELECT * FROM users WHERE user_id = ? OR `email` = ? ', [$id, $id])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }

                return $result->resultRows[0];
            });
    }
    public function findByUid(string $uid): PromiseInterface{
        return $this->db->query('SELECT * FROM users WHERE `uid` = ? ', [$uid])
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }

                return $result->resultRows[0];
            });
    }


    public function update(UserModel $user, $user_id): PromiseInterface{
        return $this->findOne($user_id)
            ->then(function ($oldUser) use ($user, $user_id) {
                $query  = "UPDATE users SET 
                        `name` = ? , 
                        email = ? , 
                        mobile_number = ? , 
                        profile_photo = ? , 
                        device_token = ? , 
                        online_status = ? , 
                        user_app_version = ? , 
                        last_login = ?
                        WHERE user_id = ? ";

                return $this->db->query($query, [
                    $user->name,
                    $user->email, 
                    $user->mobile_number, 
                    $user->profile_photo, 
                    $user->device_token, 
                    $user->online_status ?? $oldUser["online_status"], 
                    $user->user_app_version, 
                    $user->last_login, 

                    $user_id
                ])->then(function () use ($oldUser) {
                    return $this->findOne($oldUser["user_id"]);
                });
            });
    }



    public function updateLastLogin(UserModel $user, $user_id): PromiseInterface{
        return $this->findOne($user_id)
            ->then(function ($oldUser) use ($user, $user_id) {
                $query  = "UPDATE users SET 
                        online_status = ? , 
                        last_login = ?
                        WHERE user_id = ? ";

                return $this->db->query($query, [
                    $user->online_status ?? $oldUser["online_status"], 
                    $user->last_login, 

                    $user_id
                ])->then(function () use ($oldUser) {
                    return $this->findOne($oldUser["user_id"]);
                });
            });
    }


    public function updateAddress(UserModel $user, $user_id): PromiseInterface{
        return $this->findOne($user_id)
            ->then(function ($oldUser) use ($user, $user_id) {
                $query  = "UPDATE users SET 
                        `address` = ? , 
                        address_lat = ? , 
                        address_long = ? , 
                        `state` = ? 
                        WHERE user_id = ? ";


                return $this->db->query($query, [
                    $user->address, 
                    $user->address_lat,
                    $user->address_long, 
                    $user->state, 

                    $user_id
                ])->then(function () use ($oldUser) {
                    return $this->findOne($oldUser["user_id"]);
                });
            });
    }


    public function create(UserModel $user): PromiseInterface {
        echo "sdsdddd";
        $query = "INSERT INTO `users` (`user_id`, `uid`, `name`, `email`, `mobile_number`, 
                    `address`, `address_lat`, `address_long`, `state`, 
                    `profile_photo`, `device_token`, `user_app_version`, `last_login`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->query($query, [
                NULL, 
                $user->uid, 
                $user->name, 
                $user->email, 
                $user->mobile_number, 
                $user->address, 
                $user->address_lat, 
                $user->address_long, 
                $user->state, 
                $user->profile_photo, 
                $user->device_token, 
                $user->user_app_version, 
                $user->last_login,
            ])->then(function () use ($user) {
                return $this->findByUid($user->uid);
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }
}


                    