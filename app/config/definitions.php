<?php


use App\UserMock\Auth;
use App\UserMock\AuthInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

return [
    PDO::class => static fn(): PDO =>
    new PDO(
        sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s;user=%s;password=%s",
            'mysql',
        '3306',
        getenv('MYSQL_DATABASE'),
            'utf8',
        getenv('MYSQL_USER'),
        getenv('MYSQL_PASSWORD'),
        ),
        options: [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    ),
    LoggerInterface::class => function (): Logger {
        $logger = new Logger('logger');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/logs/.log'));

        return $logger;
    },
    ValidatorInterface::class => function (): ValidatorInterface {
        return Validation::createValidator();
    },
    AuthInterface::class => static fn() => new Auth(),



];