<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use App\Database;

final class AppSettingsServices{
    private $db;

    public function __construct(Database $database){
        $this->db = $database->db;
    }

    public function find($key): PromiseInterface{
        $query = "SELECT  app_settings.* FROM app_settings WHERE d_key = '$key' ";

            return $this->db->query($query)
                ->then(function (QueryResult $result) {
                    return $result->resultRows[0];
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }


    public function update($key, $value, int $last_updated_by): PromiseInterface {
        $query = "UPDATE `app_settings` SET `value` = ?, last_updated_by = ? WHERE d_key = ? ";

        return $this->db->query($query, [
                $value, 
                $last_updated_by,
                $key
            ])->then(function () use ($key) {
                return $this->find($key);
            },
            function (\Exception $error) {
                return "Error: $error";
            });
    }
}


                    