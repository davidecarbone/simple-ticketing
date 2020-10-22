<?php

namespace SimpleTicketing\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Controller\TicketsController;
use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\TicketId;
use SimpleTicketing\User\User;
use SimpleTicketing\User\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketsControllerTest extends TestCase
{
    /** @var TicketsController */
    private $ticketsController;

    /** @var JWT | MockObject */
    private $jwtMock;

    /** @var TicketRepository | MockObject */
    private $ticketRepositoryMock;

    public function setUp()
    {
        parent::setUp();

        $this->ticketRepositoryMock = $this->createMock(TicketRepository::class);
        $this->jwtMock = $this->createMock(JWT::class);
        $this->ticketsController = new TicketsController($this->ticketRepositoryMock, $this->jwtMock);
    }

    /** @test */
    public function post_tickets_successful_should_respond_201_with_a_message()
    {
        $this->ticketRepositoryMock
            ->expects($this->once())
            ->method('insert')
            ->willReturn(new TicketId());

	    $this->jwtMock
		    ->expects($this->once())
		    ->method('decode')
		    ->willReturn([
			    'id' => '4d8f38dc-05d4-42a6-93fe-69a72fc533b1',
			    'username' => 'admin',
			    'password' => 'test',
			    'type' => 'ADMIN',
			    'fullName' => 'admin test'
		    ]);

	    $request = Request::create('/tickets', 'POST', [], [], [], [], json_encode([
		    'message' => 'test message'
	    ]));

        $response = $this->ticketsController->postTicket($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('message', $responseContent);
    }

    /**
     * @test
     */
    public function post_tickets_should_respond_400_when_message_is_missing()
    {
        $this->ticketRepositoryMock
            ->expects($this->never())
            ->method('insert');

	    $this->jwtMock
		    ->expects($this->once())
		    ->method('decode')
		    ->willReturn([
			    'id' => '4d8f38dc-05d4-42a6-93fe-69a72fc533b1',
			    'username' => 'admin',
			    'password' => 'test',
			    'type' => 'ADMIN',
			    'fullName' => 'admin test'
		    ]);

	    $request = Request::create('/tickets', 'POST', [], [], [], []);

        $response = $this->ticketsController->postTicket($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseContent);
    }
}
