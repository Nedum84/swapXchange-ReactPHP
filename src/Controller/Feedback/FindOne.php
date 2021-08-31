<?php

namespace App\Controller\Feedback;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FeedbackServices;

final class FindOne{
    private $feedbackServices;

    public function __construct(Database $db){
        $this->feedbackServices = new FeedbackServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $id){

        return $this->feedbackServices->findOne($id)
            ->then(function(array $response) {
                if(\count($response)==0)
                    return JsonResponse::badRequest("Nothing found");
                return JsonResponse::ok(["feedback" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
