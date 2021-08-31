<?php

namespace App\Controller\Feedback;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FeedbackServices;

final class FindAll{
    private $feedbackServices;

    public function __construct(Database $db){
        $this->feedbackServices = new FeedbackServices($db);
    }

    public function __invoke(ServerRequestInterface $request, $status = "all"){


        return $this->feedbackServices->findAll($status)
        ->then(function(array $response) {
            return JsonResponse::ok(["feedback" => $response ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
