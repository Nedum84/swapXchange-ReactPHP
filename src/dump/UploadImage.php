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

        assert($body instanceof \Psr\Http\Message\StreamInterface);
        assert($body instanceof \React\Stream\ReadableStreamInterface);


        return new \React\Promise\Promise(function ($resolve) use ($body) {
            $bytes = 0;
            $requestBody='';
            $body->on('data', function ($chunk) use (&$bytes, &$requestBody) {
                $requestBody .= $chunk;
                $bytes += \count($chunk);
            });
            $body->on('close', function () use (&$bytes, $resolve,&$requestBody) {

                $body               = json_decode($requestBody, true);
                $image_file         = $body['image_file']??""; 
                $file_name          = $body['file_name']??"swapxchange.jpg";

                \var_dump($image_file);

                $resolve(
                    $this->imageUploadServices->uploadFile($image_file, $file_name) 
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

                $resolve(new \React\Http\Message\Response(
                    200,
                    [],
                    "Received $bytes bytes\n"
                ));
            });
        });
    }
}
