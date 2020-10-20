<?php

namespace SimpleTicketing\Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use SimpleTicketing\User\User;
use SimpleTicketing\User\UserId;

class UserTest extends TestCase
{
    /** @test */
    public function can_be_built_from_array_and_exported_to_array()
    {
        $userId = new UserId();

        $user = User::fromArray([
            'id' => $userId,
            'username' => 'admin',
            'password' => 'test',
	        'type' => 'ADMIN',
	        'fullName' => 'admin test'
        ]);

        $userArray = $user->toArray();

        $this->assertEquals($userId, $userArray['id']);
        $this->assertEquals('admin', $userArray['username']);
        $this->assertEquals('admin test', $userArray['fullName']);
        $this->assertEquals('ADMIN', $userArray['type']);
        $this->assertArrayHasKey('password', $userArray);
    }
}
