<?php

namespace App\Controller\ProductImage;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductImageServices;

final class DeleteProductImage{
    private $productImageServices;
    private $projectRoot;
    private $filesystem; 

    public function __construct(Database $db, string $projectRoot, \React\Filesystem\Filesystem $filesystem){
        $this->productImageServices = new ProductImageServices($db);
        $this->projectRoot = $projectRoot;
        $this->filesystem = $filesystem;
    }

    public function __invoke(ServerRequestInterface $request, string $id){

        return $this->productImageServices->delete($id, $this->projectRoot, $this->filesystem)
            ->then(function(\App\Models\ServiceResponse $response) {
                if($response->success)
                    return JsonResponse::ok([], "Successfully deleted the image");
                return JsonResponse::badRequest($response->data);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
