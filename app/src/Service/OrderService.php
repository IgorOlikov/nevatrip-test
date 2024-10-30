<?php

namespace App\Service;

use App\Exception\HttpException;
use App\Repository\OrderRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Random\RandomException;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private \PDO $pdo
    )
    {
    }


    /**
     * @param int $eventId
     * @param string $eventDate
     * @param int $ticketAdultPrice
     * @param int $ticketAdultQuantity
     * @param int $ticketKidPrice
     * @param int $ticketKidQuantity
     * @return array
     * @throws GuzzleException
     * @throws RandomException
     * @throws HttpException
     */
    public function addOrder(
        int $eventId,
        string $eventDate,
        int $ticketAdultPrice,
        int $ticketAdultQuantity,
        int $ticketKidPrice,
        int $ticketKidQuantity
    ): array
    {
        /** transaction */

        try {

            $this->pdo->beginTransaction();

            $barcode = $this->bookOrder(
                $eventId,
                $eventDate,
                $ticketAdultPrice,
                $ticketAdultQuantity,
                $ticketKidPrice,
                $ticketKidQuantity
            );

            $equalPrice = $this->calcEqualPrice($ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity);

            $order = $this->orderRepository->createOrder(
                $eventId,
                $eventDate,
                $ticketAdultPrice,
                $ticketAdultQuantity,
                $ticketKidPrice,
                $ticketKidQuantity,
                $equalPrice
            );

            $this->pdo->commit();

            $this->approveBooking($barcode);

        } catch (Exception $exception) {
            $this->pdo->rollBack();

            throw $exception;
        }

        return $order;
    }

    /**
     * @param int $eventId
     * @param string $eventDate
     * @param int $ticketAdultPrice
     * @param int $ticketAdultQuantity
     * @param int $ticketKidPrice
     * @param int $ticketKidQuantity
     * @param int|null $retries
     * @return int
     * @throws GuzzleException
     * @throws RandomException
     * @throws HttpException
     */
    public function bookOrder(
        int $eventId,
        string $eventDate,
        int $ticketAdultPrice,
        int $ticketAdultQuantity,
        int $ticketKidPrice,
        int $ticketKidQuantity,
        ?int $retries = 10
    ): int
    {
        if ($retries <= 0) {
            throw new HttpException(['error' => 'max api calls attempts'], 400);
        }

        $barcode = $this->generateUniqueBarcode();

        if ($this->orderRepository->orderExistByBarcode($barcode)) {
            $this->bookOrder($eventId, $eventDate, $ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity, $retries - 1);
        }

        $response = $this->bookOrderApiMockRequest(
            $eventId,
            $eventDate,
            $ticketAdultPrice,
            $ticketAdultQuantity,
            $ticketKidPrice,
            $ticketKidQuantity,
            $barcode
        );

        $responseContent = json_decode($response->getBody()->getContents(), true);


        if ($responseContent['error'] === 'barcode already exists') {
            $this->bookOrder($eventId, $eventDate, $ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity, $retries - 1);
        }
        elseif ($responseContent['message'] == 'order successfully booked') {
            return $barcode;
        }

        throw new HttpException($responseContent, $response->getStatusCode()); // https://api.site.com/book Server Error
    }

    /**
     * @param int $barcode
     * @throws GuzzleException
     * @throws HttpException
     */
    public function approveBooking(int $barcode): void
    {
        $response = $this->approveOrderApiMockRequest($barcode);

        $responseContent = json_decode($response->getBody()->getContents(), true);

        if ($responseContent['error']) {
            throw new HttpException($responseContent, $response->getStatusCode());
        }
    }

    /**
     * @param int $eventId
     * @param string $eventDate
     * @param int $ticketAdultPrice
     * @param int $ticketAdultQuantity
     * @param int $ticketKidPrice
     * @param int $ticketKidQuantity
     * @param int $barcode
     * @return Response
     * @throws GuzzleException
     */
    public function bookOrderApiMockRequest(
        int $eventId,
        string $eventDate,
        int $ticketAdultPrice,
        int $ticketAdultQuantity,
        int $ticketKidPrice,
        int $ticketKidQuantity,
        int $barcode
    ): Response
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'order successfully booked'])),
            new Response(422, ['Content-Type' => 'application/json'], json_encode(['error' => 'barcode already exists']))
        ]);

        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);

        return $client->request(
            'POST',
            'https://api.site.com/book',
            [
                'json' => [
                    'order' => [
                        'event_id' => $eventId,
                        'event_date' => $eventDate,
                        'ticket_adult_price' => $ticketAdultPrice,
                        'ticket_adult_quantity' => $ticketAdultQuantity,
                        'ticket_kid_price' => $ticketKidPrice,
                        'ticket_kid_quantity' => $ticketKidQuantity,
                        'barcode' => $barcode
                    ]
                ],
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']
            ]
        );
    }

    /**
     * @param int $barcode
     * @return Response
     * @throws GuzzleException
     */
    public function approveOrderApiMockRequest(int $barcode): Response
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'order successfully approved'])),
            new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'event cancelled'])),
            new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'no tickets'])),
            new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'no seats'])),
            new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'fan removed'])),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);

        return $client->request(
            'POST',
            'https://api.site.com/approve',
            [
                'json' => ['barcode' => $barcode],
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']
            ]
        );
    }

    /**
     * @param int $ticketAdultPrice
     * @param int $ticketAdultQuantity
     * @param int $ticketKidPrice
     * @param int $ticketKidQuantity
     * @return int
     */
    public function calcEqualPrice(
        int $ticketAdultPrice,
        int $ticketAdultQuantity,
        int $ticketKidPrice,
        int $ticketKidQuantity
    ): int
    {
        return ($ticketAdultPrice * $ticketAdultQuantity) + ($ticketKidPrice * $ticketKidQuantity);
    }

    /**
     * @throws RandomException
     * @return int
     */
    public function generateUniqueBarcode(): int
    {
        return random_int(120,120);
    }





}