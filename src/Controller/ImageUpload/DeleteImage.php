<?php

namespace App\Controller\ImageUpload;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ImageUploadServices;

final class DeleteImage{
    private $imageUploadServices;

    public function __construct(string $projectRoot, \React\Filesystem\Filesystem $filesystem){
        $this->imageUploadServices = new ImageUploadServices($projectRoot, $filesystem);
    }

    public function __invoke(ServerRequestInterface $request, string $image_path){

        return $this->imageUploadServices->delete($image_path)
            ->then(function($response) {
                if(\gettype($response)!="array")
                    return JsonResponse::badRequest("No response found");
                return JsonResponse::ok("Image successfully removed");
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
