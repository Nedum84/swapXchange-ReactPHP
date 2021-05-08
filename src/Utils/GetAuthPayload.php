<?php

namespace App\Utils;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use App\JWTAuth\JwtEncoder;

final class GetAuthPayload{
    private const HEADER_VALUE_PATTERN = "/Bearer\s+(.*)$/i";


    public static function getPayload(ServerRequestInterface $request): object {
        $jwt = self::extractToken($request);
        if (empty($jwt)) {
            return [];
        }

        $encoder = new JwtEncoder();
        $payload = $encoder->decode($jwt);
        $data = (object) $payload["data"];
        $data->user_id = (int) $data->user_id;
        
        return $data??(object)[];
    }

    private function extractToken(ServerRequestInterface $request): ?string{
        $authHeader = $request->getHeader('Authorization');
        if (empty($authHeader)) {
            return null;
        }

        if (preg_match(self::HEADER_VALUE_PATTERN, $authHeader[0], $matches)) {
            return $matches[1];
        }

        return null;
    }
}
