<?php
namespace App\Controller\Feedback;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FeedbackServices;


final class UpdateFeedback{
    private $feedbackServices;

    public function __construct(Database $db){
        $this->feedbackServices = new FeedbackServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $id){
        $body = json_decode((string) $request->getBody(), true);
        $status        = $body['status'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->feedbackServices->update($id, $user_id, $status) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::ok(["feedback" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
