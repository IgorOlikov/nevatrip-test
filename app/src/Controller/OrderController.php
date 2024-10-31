<?php

namespace App\Controller;

use App\Service\OrderService;
use App\UserMock\AuthInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class OrderController
{
    public function __construct(
        private OrderService $orderService,
        private ValidatorInterface $validator,
        private AuthInterface $auth
    )
    {
    }

    public function store(Request $request, Response $response): Response
    {
        $jsonContent = $request->getBody()->getContents();
        $decodedContent = json_decode($jsonContent,true);

        $constraints = new Assert\Collection([
            'event_id' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
                new Assert\Length(min: 1, max: 11)
            ],
            'event_date' => [
                new Assert\Type('string'),
                new Assert\DateTime('Y-m-d H:i:s'),
                new Assert\Length(max: 19)
            ],
            'ticket_adult_price' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
                new Assert\Range(min: 1),
                new Assert\Length(min: 1, max: 11)
            ],
            'ticket_adult_quantity' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
                new Assert\Range(min: 0),
                new Assert\Length(min: 1, max: 11)
            ],
            'ticket_kid_price' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
                new Assert\Range(min: 1),
                new Assert\Length(min: 1, max: 11)
            ],
            'ticket_kid_quantity' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
                new Assert\Range(min: 0),
                new Assert\Length(min: 1, max: 11)
            ]
        ]);

        $errors = $this->validator->validate($decodedContent, $constraints);

        if (count($errors) > 0) {
            $errArr = ['error' => 'Validation failed!', 'errors' => []];

            foreach ($errors as $error) {
                $errArr['errors'][] = "{$error->getPropertyPath()} {$error->getMessage()}";
            }
            $response = $response->withStatus(422);
            $response->getBody()->write(json_encode($errArr));

            return $response;
        }

        $order = $this->orderService->addOrder(
            $decodedContent['event_id'],
            $decodedContent['event_date'],
            $decodedContent['ticket_adult_price'],
            $decodedContent['ticket_adult_quantity'],
            $decodedContent['ticket_kid_price'],
            $decodedContent['ticket_kid_quantity'],
            $this->auth->getUserId()
        );

        $response = $response->withStatus(201);

        $response->getBody()->write(json_encode(['order' => $order]));

        return $response;
    }

}