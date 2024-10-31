create table if not exists orders(
    id  int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY UNIQUE NOT NULL,
    event_id int(11) UNSIGNED NOT NULL,
    event_date datetime NOT NULL,
    ticket_adult_price int(11) UNSIGNED NOT NULL CHECK (ticket_adult_price >= 1),
    ticket_adult_quantity int(11) UNSIGNED NOT NULL,
    ticket_kid_price int(11) UNSIGNED NOT NULL CHECK (ticket_kid_price >= 1),
    ticket_kid_quantity int(11) UNSIGNED NOT NULL,
    barcode varchar(120) NOT NULL,
    user_id int(11) UNSIGNED NOT NULL,
    equal_price int(11) UNSIGNED NOT NULL CHECK (equal_price >=1),
    created datetime DEFAULT CURRENT_TIMESTAMP() NOT NULL
);

create index idx_orders_barcode ON orders(barcode)