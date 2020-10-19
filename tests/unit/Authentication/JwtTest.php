<?php

namespace SimpleTicketing\Tests\Unit\Authentication;

use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Password\Password;
use SimpleTicketing\Tests\ContainerAwareTest;

class JwtTest extends ContainerAwareTest
{
    public function testPasswordValidateAgainstHash()
    {
        $password = new Password('$fV1v!-_er');
        $hash = '$2y$10$Ps8vHqsLl9NizqEn64fr1uAWd..uxLDJF2u.8VW97FU7zlg8xBS8O';

        $this->assertTrue($password->validateAgainstHash($hash));
    }

    public function testJwtEncodeAndDecode()
    {
        $jwtSecret = self::$container->getParameter('jwt.secret');
        $jwt = new JWT($jwtSecret);

        $expectedUser = [
            'id' => "1",
            'username' => "admin",
            'password' => "test"
        ];

        $token = $jwt->encode($expectedUser);

        $user = $jwt->decode($token);
        $userArray = json_decode(json_encode($user), true);
        unset($userArray['exp']);

        $this->assertEquals($expectedUser, $userArray);
    }
}
