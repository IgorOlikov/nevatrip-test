<?php

namespace App\Error;

use App\Exception\HttpException;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Error
{
    private LoggerInterface $logger;


    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function handle(\Throwable $exception, Response $response): Response
    {
        $errorResponseArr = [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'code' => $exception->getCode()
        ];

        if ($exception instanceof HttpException) {
            $response->getBody()->write(json_encode($errorResponseArr));

            $response = $response->withStatus($exception->getStatusCode());
        } else {
            $response->getBody()->write(json_encode($errorResponseArr));
        }
        $this->logger->error("{$exception->getCode()}:{$exception->getMessage()}:{$exception->getFile()}:{$exception->getTraceAsString()}");

        return $response;
    }

}