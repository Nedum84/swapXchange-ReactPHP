<?php

namespace App\Controller\Feedback;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FeedbackServices;



final class CreateFeedback{
    private $feedbackServices;

    public function __construct(Database $db){
        $this->feedbackServices = new FeedbackServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $message        = $body['message'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->feedbackServices->create($user_id, $message) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::created(["feedback" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );

    }
}
