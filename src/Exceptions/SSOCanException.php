<?php

namespace RistekUSDI\SSO\Exceptions;

use Illuminate\Auth\AuthenticationException;

class SSOCanException extends AuthenticationException
{
    /**
     * SSO Callback Error
     *
     * @param string|null     $message  [description]
     * @param \Throwable|null $previous [description]
     * @param array           $headers  [description]
     * @param int|integer     $code     [description]
     */
    public function sss__construct(string $error = '')
    {

    }
}
