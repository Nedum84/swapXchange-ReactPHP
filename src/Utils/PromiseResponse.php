<?php

namespace App\Utils;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;

final class PromiseResponse{

    public static function resolvePromise($data = null):PromiseInterface{
        $deferred = new Deferred();
        $promise = $deferred->promise();

        $deferred->resolve($data??[]);
        return $promise;
    }


    public static function rejectPromise($data = null):PromiseInterface{
        $deferred = new Deferred();
        $promise = $deferred->promise();

        $deferred->reject($data??[]);
        return $promise->otherwise( function($errr) use ($data) {
            return $data??[];
        });
    }

}


                    