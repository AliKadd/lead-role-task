<?php

namespace App\Exceptions;

use Exception;

class OptimisticLockException extends Exception
{
    protected $message;

    public function __construct($message = "Optimistic lock exception", $code = 409) {
        parent::__construct($message, $code);
    }
}
