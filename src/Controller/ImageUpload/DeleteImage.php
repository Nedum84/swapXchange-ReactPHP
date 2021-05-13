<?php

namespace App\Controller\Category;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ImageUploadServices;

final class DeleteImage{
    private $imageUploadServices;

    public function __construct(string $dir, \React\Filesystem\Filesystem $filesystem){
        $this->imageUploadServices = new ImageUploadServices($db, $filesystem);
    }

    public function __invoke(ServerRequestInterface $request, string $category_id){
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->imageUploadServices->findOne($category_id, $user_id)
            ->then(function(array $category) {
                if(\count($category)==0)
                    return JsonResponse::badRequest("No category found");
                return JsonResponse::ok(["category" => $category]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
