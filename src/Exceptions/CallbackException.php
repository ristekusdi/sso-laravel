<?php

namespace RistekUSDI\SSO\Exceptions;

use Exception;

class CallbackException extends Exception
{
    public function report()
    {
        # code...
    }

    public function render($request)
    {
        $status = 401;
        if (!empty($this->getCode())) {
            $status = $this->getCode();
        }
        
        return response()->view("sso-web.errors.{$status}", ['e' => $this], $status);
    }
}