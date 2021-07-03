<?php

namespace App\JWTAuth;

use Firebase\JWT\JWT;
use App\Config\Config;

final class JwtEncoder{

private $privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDFixcqSaV/2g6tqDXXQr1YVBoa8PpaOvwHtzCwvMnSEXGSouxn
ge1b1iZ1L+lhQmGmF8gOTlOHZdMYb6bvG5ScwHz7nOsGdG1waPIGpFXjp/wetiU6
9O2B2twLwcnhUKLSkwtdfymFpIUbKIsg3bGuR/z/R6Wx4LjLJbNZjlgt2wIDAQAB
AoGABStdoNKqjQz0w2CagBaUA+K3iCr0MjZG8CDAGm/mTCP+t9qhmxfQUU3qVbi6
P7xP99u5dX5hOzLT8ljBopIzMlcfY5dTglqhJB0anxyMmIiR2qMayWQVfj4DqAix
MnYv128IZjLRiLcS07SYfikr1uxvhnIheOW1YbBOX9KL0NECQQDwcXd70KaYtbn5
oA/ktS38uDuCjFt386uBGlnRcfIIdXSwFJo6ZJrGObjwI05rqVkruFLojksNVMFU
9gRrVU0zAkEA0lMPSuJL1dJ4c2PYH1qwa3fzxt1QgXirSOd0FqvzjbE6050qYerA
ruUERATkQkQG2ezFM6pt9rn1j6Xgt5cMuQJAZVjubBn+ns+6nCWDjXtw7t0Y+GYB
CAaFe92HjmjhA/++N5n3iDVvp64c7dtz6p1vIKaJC80uhWf8NbudEUDbUQJAJz/E
zM6qJD7gp3fsbv13iraZ0XSff2nLXRGEQm7YRoraVX8w15D9YCXww9i91/fl52kd
6+RUHQTa5HGqG9ieUQJBANmg70MNytJGZxjBs5DK+IUucbMOjcRh5IONW/lH/ik+
JYTw0IGxGBWDaf3uLW1L46oRYyedA4hpPoGugZ7XYuU=
-----END RSA PRIVATE KEY-----
EOD;
private $publicKey =  <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDFixcqSaV/2g6tqDXXQr1YVBoa
8PpaOvwHtzCwvMnSEXGSouxnge1b1iZ1L+lhQmGmF8gOTlOHZdMYb6bvG5ScwHz7
nOsGdG1waPIGpFXjp/wetiU69O2B2twLwcnhUKLSkwtdfymFpIUbKIsg3bGuR/z/
R6Wx4LjLJbNZjlgt2wIDAQAB
-----END PUBLIC KEY-----
EOD;

    public function encode(array $payload): string{
        // return JWT::encode($payload, Config::$privateKey, 'RS256');
        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    public function decode(string $jwt): array{
        // $decoded = JWT::decode($jwt, Config::$publicKey, ['HS256']);
        
        try {
            $decoded = JWT::decode($jwt, $this->publicKey, ['RS256']);
            return (array)$decoded;
        }catch(\UnexpectedValueException $er){
            return [];
        }catch(\Firebase\JWT\ExpiredException $er){
            return [];
        }
    }
}

?>