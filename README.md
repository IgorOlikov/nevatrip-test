Инструкция пл установке.

Одной строкой из корневой папки проекта.
```bash
make build && make up && make install && make table
```
Проверить работоспособность можно curl запросом.

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
Схема базы данных для задачи № 2

Максимально нормализированная схема, что может иметь негативный эффект из - за сложных запросов с джойнами.

![](https://github.com/IgorOlikov/nevatrip-test/blob/main/Task-2/task2-schema.png)