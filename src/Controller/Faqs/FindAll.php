<?php
namespace App\Controller\Faqs;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FaqsServices;

final class FindAll{
    private $faqsServices;

    public function __construct(Database $db){
        $this->faqsServices = new FaqsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){

        return $this->faqsServices->findAll()
        ->then(function(array $response) {
            return JsonResponse::ok(["faq" => $response ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
