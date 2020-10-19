<?php

namespace SimpleTicketing\Tests\Integration\User;

use SimpleTicketing\Password\Password;
use SimpleTicketing\Tests\ContainerAwareTest;
use SimpleTicketing\Repository\UserRepository;
use SimpleTicketing\User\User;

class RepositoryTest extends ContainerAwareTest
{
    /** @var UserRepository */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = self::$container->get(UserRepository::class);
    }

    /** @test */
    public function can_find_users_by_username_and_password()
    {
        $user = $this->repository->findByUsernameAndPassword('user', new Password('user'));

        $this->assertInstanceOf(User::class, $user);
    }
}
