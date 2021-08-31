<?php

namespace App\Controller\Faqs;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FaqsServices;


final class CreateFaqs{
    private $faqsServices;

    public function __construct(Database $db){
        $this->faqsServices = new FaqsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $question        = $body['question'] ?? ''; 
        $answer        = $body['answer'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->faqsServices->create($user_id, $question, $answer) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::created(["faq" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );

    }
}
