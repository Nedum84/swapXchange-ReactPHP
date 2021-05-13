<?php

namespace App;

use React\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class JsonResponse{

    private function response(int $statusCode, $data = null):Response{
        $body = $data ? json_encode($data) : null;
        return new Response($statusCode, ['Content-Type' => 'application/json'], $body);
    }
    public static function ok($data = null, $message=null){
        $status = 200;
        $res = [
            'status' => $status,
            'success' => true,
            'message' => $message??'Successful request',
            'data' => $data
        ];
        return self::response($status, $res);
    }

    public static function noContent(){
        $status = 204;
        $res = [
            'status' => $status,
            'success' => false,
            'message' => 'No content',
        ];
        return self::response($status, $res);
        return new self(204);
    }
    public static function internalServerError(string $reason=null){
        $status = 500;
        $res = [
            'status' => $status,
            'success' => false,
            'message' => $reason??'Server error'
        ];
        return self::response($status, $res);
    }

    public static function created($data = null, $message=null){
        $status = 201;
        $res = [
            'status' => $status,
            'success' => true,
            'message' => $message??'Successfully created',
            'data' => $data??'',
        ];
        return self::response($status, $res);
    }

    public static function badRequest(string $error=null){
        $status = 400;
        $res = [
            'status' => $status,
            'success' => false,
            'message' => $error??'Error occured',
        ];
        return self::response($status, $res);
    }

    public static function notFound(string $error=null){
        $status = 404;
        $res = [
            'status' => $status,
            'success' => false,
            'message' => $error??'Resource Not found',
        ];
        return self::response($status, $res);
    }

    public static function unauthorized($error = null){
        $status = 401;
        $res = [
            'status' => $status,
            'success' => false,
            'message' => $error??'Unauthorized',
        ];
        return self::response($status, $res);
    }
}
