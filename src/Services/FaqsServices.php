<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;

final class FaqsServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function findAll(): PromiseInterface{
        $query = "SELECT  faqs.* FROM faqs ";

            return $this->db->query($query)
                ->then(function (QueryResult $result) {

                    return $result->resultRows;
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }


    public function findOne($faq_id): PromiseInterface{
        return $this->db->query("SELECT * FROM faqs WHERE faq_id = $faq_id ")
            ->then(function (QueryResult $result) {
                if (empty($result->resultRows)) {
                    return [];
                }
                return $result->resultRows[0];
        });
    }

    public function update(int $faq_id, int $user_id, string $question, string $answer): PromiseInterface {
        $query = "UPDATE `faqs` SET `question` = ?, answer = ?, added_by = ? WHERE faq_id = ? ";

        return $this->db->query($query, [
                $question, 
                $answer,
                $user_id,
                $faq_id
            ])->then(function () use ($faq_id) {
                return $this->findOne($faq_id);
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }

    public function create(int $user_id, string $question, string $answer): PromiseInterface {
        $query = "INSERT INTO `faqs` (`faq_id`, `question`, `answer`, `category`, `added_by`) VALUES (?, ?, ?, ?, ?)";

        return $this->db->query($query, [
                NULL, 
                $question, 
                $answer, 
                $category='', 
                $user_id
            ])->then(function () use ($user_id, $product_id) {
                return $this->findOne('LAST_INSERT_ID()');
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }
}


                    