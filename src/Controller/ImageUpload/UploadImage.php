<?php

namespace App\Controller\ImageUpload;

use App\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ImageUploadServices;



final class UploadImage{
    private $imageUploadServices;

    public function __construct(string $dir, \React\Filesystem\Filesystem $filesystem){
        $this->imageUploadServices = new ImageUploadServices($dir, $filesystem);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $image_file         = $body['image_file']; 
        $file_name          = $body['file_name']; 

        return $this->imageUploadServices->uploadFile($image_file, $file_name) 
            ->then(
                function ($response) {
                    echo gettype($response);
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::created($response);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
