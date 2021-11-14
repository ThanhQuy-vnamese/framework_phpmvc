<?php


namespace App\Core\Lib;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

class Token
{
    private array $payload;

    public function setPayload(array $payload) {
        $this->payload = $payload;
    }

    public function encode(string $privateKey, string $alg = 'HS256'): string {
        return JWT::encode($this->payload, $privateKey, $alg);
    }

    public function decode($jwt, string $publicKey, array $allowedAlg = ['HS256']): object
    {
        return JWT::decode($jwt, $publicKey, $allowedAlg);
    }

    public function parseKeySet(array $jwk) {
        return JWK::parseKey($jwk);
    }
}