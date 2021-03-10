<?php

namespace RistekUSDI\SSO\Exceptions;

class CallbackException extends \RuntimeException
{
    /**
     * Callback Error
     *
     * @param string|null     $message  [description]
     * @param \Throwable|null $previous [description]
     * @param array           $headers  [description]
     * @param int|integer     $code     [description]
     */
    public function __construct(string $error = '')
    {
        $message = '[SSO Error] ' . $error;

        parent::__construct($message);
    }
}
