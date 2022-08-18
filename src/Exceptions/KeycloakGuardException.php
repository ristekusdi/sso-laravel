<?php
namespace RistekUSDI\SSO\Exceptions;

class KeycloakGuardException extends \UnexpectedValueException
{
    public function __construct(string $message, int $code = 401)
    {
        $this->message = "[Keycloak Token Guard] {$message}";
        $this->code = $code;
    }
}