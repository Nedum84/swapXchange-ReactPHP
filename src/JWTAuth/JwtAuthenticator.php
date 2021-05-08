<?php

namespace App\JWTAuth;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

final class JwtAuthenticator{
    private const HEADER_VALUE_PATTERN = "/Bearer\s+(.*)$/i";

    private $encoder;

    public function __construct(JwtEncoder $encoder){
        $this->encoder = $encoder;
    }

    public function validate(ServerRequestInterface $request): bool{
        $jwt = $this->extractToken($request);
        if (empty($jwt)) {
            return false;
        }

        $payload = $this->encoder->decode($jwt);
        return $payload !== null && count($payload)!==0;
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
