<?php

namespace SimpleTicketing\Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use SimpleTicketing\User\UserType;
use SimpleTicketing\User\UserTypeException;

class UserTypeTest extends TestCase
{
    /** @test */
    public function can_be_built_with_types_admin_or_customer()
    {
        $admin = new UserType('ADMIN');
        $customer = new UserType('CUSTOMER');

        $this->assertInstanceOf(UserType::class, $admin);
        $this->assertInstanceOf(UserType::class, $customer);
    }

	/** @test */
	public function throws_exception_when_type_is_not_valid()
	{
		$this->expectException(UserTypeException::class);

		$admin = new UserType('UNKNOWN');
	}
}
