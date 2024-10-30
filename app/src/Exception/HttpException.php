<?php

namespace App\Exception;


use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{

    public function __construct(array $message = ['message' => 'http error'], private ?int $statusCode = 400, ?int $code = 0, ?Throwable $previous = null)
    {

        parent::__construct(json_encode($message), $code, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }




}