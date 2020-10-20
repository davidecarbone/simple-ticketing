<?php

namespace SimpleTicketing\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Controller\LoginController;
use SimpleTicketing\Repository\UserRepository;
use SimpleTicketing\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends TestCase
{
    /** @var LoginController */
    private $loginController;

    /** @var JWT | MockObject */
    private $jwtMock;

    /** @var UserRepository | MockObject */
    private $userRepositoryMock;

    public function setUp()
    {
        parent::setUp();

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->jwtMock = $this->createMock(JWT::class);
        $this->loginController = new LoginController($this->jwtMock, $this->userRepositoryMock);
    }

    /** @test */
    public function post_login_successful_should_respond_200_with_jwt()
    {
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByUsernameAndPassword')
            ->willReturn(User::fromArray([
                'id' => '4d8f38dc-05d4-42a6-93fe-69a72fc533b1',
                'username' => 'test',
                'password' => '$2y$12$S3RahWt0Uh7DsjOXaiOhceqwy2Ryi.rc/ptYpUCKgK4Fsm1hX9jMS',
	            'type' => 'CUSTOMER',
	            'fullName' => 'Test test'
            ]));

	    $request = Request::create('/login', 'POST', [], [], [], [], json_encode([
		    'username' => 'test',
		    'password' => 'test'
	    ]));

        $response = $this->loginController->login($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('JWT', $responseContent);
    }

    /**
     * @test
     * @dataProvider invalidRequestDataProvider
     */
    public function post_login_should_respond_400_when_username_or_password_are_missing($request)
    {
        $this->userRepositoryMock
            ->expects($this->never())
            ->method('findByUsernameAndPassword');

        $response = $this->loginController->login($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseContent);
    }

    public function invalidRequestDataProvider()
    {
	    $requestWithNoUsername = Request::create('/login', 'POST', [], [], [], [], json_encode([
		    'password' => 'test'
	    ]));

        $requestWithNoPassword = Request::create('/login', 'POST', [], [], [], [], json_encode([
	        'username' => 'test'
        ]));

        return [
            [$requestWithNoUsername],
            [$requestWithNoPassword]
        ];
    }

    /** @test */
    public function post_login_should_respond_401_when_username_and_password_are_incorrect()
    {
        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByUsernameAndPassword')
            ->willReturn(null);

        $request = Request::create('/login', 'POST', [], [], [], [], json_encode([
	        'username' => 'test',
	        'password' => 'wrong_password'
        ]));

        $response = $this->loginController->login($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseContent);
    }
}
