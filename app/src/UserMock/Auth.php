<?php

namespace App\UserMock;

class Auth implements AuthInterface
{
    public function getUserId(): int
    {
        return mt_rand(1,500);
    }
}