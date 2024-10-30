<?php

namespace App\Repository;

readonly class OrderRepository
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function createOrder(
        int $eventId,
        string $eventDate,
        int $ticketAdultPrice,
        int $ticketAdultQuantity,
        int $ticketKidPrice,
        int $ticketKidQuantity,
        int $equalPrice
    ): array
    {
        $sql = 'insert into orders (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created)' .
                'values (:event_id, :event_date, :ticket_adult_price, :ticket_adult_quantity, :ticket_kid_price, :ticket_kid_quantity, :barcode, :equal_price, NOW())';

        $statement = $this->pdo->prepare($sql);

        $statement->bindValue(':event_id', $eventId, \PDO::PARAM_INT);
        $statement->bindValue(':event_date', $eventDate);
        $statement->bindValue(':ticket_adult_price', $ticketAdultPrice, \PDO::PARAM_INT);
        $statement->bindValue(':ticket_adult_quantity', $ticketAdultQuantity, \PDO::PARAM_INT);
        $statement->bindValue(':ticket_kid_price', $ticketKidPrice, \PDO::PARAM_INT);
        $statement->bindValue(':ticket_kid_quantity', $ticketKidQuantity, \PDO::PARAM_INT);
        $statement->bindValue(':equal_price', $equalPrice, \PDO::PARAM_INT);

        $statement->execute();

        $lastInsertedId = $this->pdo->lastInsertId();

        $sql = 'select * from orders where id = :lastInsertedId';

        $statement = $this->pdo->prepare($sql);

        $statement->bindValue(':lastInsertedId', $lastInsertedId, \PDO::PARAM_INT);

        $statement->execute();

        return $statement->fetchAll();
    }

    public function orderExistByBarcode(int $barcode): bool
    {
        $sql = 'select * from orders o where o.barcode = :barcode limit 1';

        $statement = $this->pdo->prepare($sql);

        $statement->bindValue(':barcode', $barcode, \PDO::PARAM_INT);

        $statement->execute();

        $order = $statement->fetchAll();

        if (empty($order)) {
            return false;
        }
        return true;
    }

}