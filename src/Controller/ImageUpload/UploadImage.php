<?php

namespace App\Controller\ImageUpload;

use App\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ImageUploadServices;



final class UploadImage{
    private $imageUploadServices;

    public function __construct(string $projectRoot, \React\Filesystem\Filesystem $filesystem){
        $this->imageUploadServices = new ImageUploadServices($projectRoot, $filesystem);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = $request->getBody();
        $host = $request->getHeader('Host')[0];
        $host = "http://$host/";

        assert($body instanceof \Psr\Http\Message\StreamInterface);
        assert($body instanceof \React\Stream\ReadableStreamInterface);


        return new \React\Promise\Promise(function ($resolve) use ($body, $host) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve,&$requestBody, $host) {
                $body               = json_decode($requestBody, true);
                $image_file         = $body['image_file']??""; 
                $file_name          = $body['file_name']??"swapxchange.jpg";

                $resolve(
                    $this->imageUploadServices->uploadFile($image_file, $file_name, $host) 
                    ->then(
                        function ($response) {
                            if(gettype($response)!=="array"){
                                return JsonResponse::badRequest($response);
                            };
                            return JsonResponse::created($response);
                        },
                        function ($error) {
                            return JsonResponse::badRequest($error->getMessage()??$error);
                        }
                    )
                );

            });
        });
    }
}
