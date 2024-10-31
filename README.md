Запрос

```bash
curl -X POST http://127.0.0.1:80/order \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{
  "event_id": 22,
  "event_date": "2022-10-05 13:00:00",
  "ticket_adult_price": 700,
  "ticket_adult_quantity": 1,
  "ticket_kid_price": 450,
  "ticket_kid_quantity": 0
}'
```