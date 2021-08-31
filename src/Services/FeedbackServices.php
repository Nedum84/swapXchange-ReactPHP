<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;

final class FeedbackServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAll($status="all"): PromiseInterface{
        if($status =="all"||$status==""||$status==null){
            $subQuery = "";
        }else{
            $subQuery = "WHERE status = '$status' ";
        }
        $query = "SELECT  feedback.* FROM feedback  $subQuery ORDER BY id DESC  ";

            return $this->db->query($query)
                ->then(function (QueryResult $result) {

                    return $result->resultRows;
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }


    public function findOne($id): PromiseInterface{
        return $this->db->query("SELECT * FROM feedback WHERE id = $id ")
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
        });
    }

    public function update(int $id, int $user_id, $status): PromiseInterface {
        $query = "UPDATE `feedback` SET `status` = ?, resolved_by = ? WHERE id = ? ";

        return $this->db->query($query, [
                $status, 
                $user_id,
                $id
            ])->then(function () use ($id) {
                return $this->findOne($id);
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }

    public function create(int $user_id, string $message): PromiseInterface {
        $query = "INSERT INTO `feedback` (`id`, `user_id`, `message`) VALUES (?, ?, ?)";

        return $this->db->query($query, [
            NULL, 
            $user_id, 
            $message
        ])->then(function () {
            return $this->findOne('LAST_INSERT_ID()');
        },
        function (\Exception $error) {
            return "Error: $error";
        });
        
    }
}


                    