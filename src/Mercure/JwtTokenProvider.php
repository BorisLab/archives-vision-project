<?php
namespace App\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

final class JwtTokenProvider implements TokenProviderInterface
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function getJwt(): string
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            Key\InMemory::plainText($this->secretKey)
        );

        return $config->builder()
            ->withClaim('mercure', [
                'publish' => ['*'], // Autorise la publication sur tous les topics
                'subscribe' => ['https://127.0.0.1:8000/users/{id}'], // Autorise l'abonnement à des topics spécifiques
            ])
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }
}