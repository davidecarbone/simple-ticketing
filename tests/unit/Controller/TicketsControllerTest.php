<?php

namespace SimpleTicketing\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Controller\TicketsController;
use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\TicketId;
use SimpleTicketing\User\User;
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
        $this->ticketsController = new TicketsController($this->ticketRepositoryMock);
    }

    /** @test */
    public function post_tickets_successful_should_respond_201_with_a_message()
    {
        $this->ticketRepositoryMock
            ->expects($this->once())
            ->method('insert')
            ->willReturn(new TicketId());

	    $request = Request::create('/tickets', 'POST', [], [], [], [], json_encode([
		    'authorId' => 'd7a8cd93-7df7-48be-a999-244e8e2f62d8',
		    'message' => 'test message'
	    ]));

        $response = $this->ticketsController->postTicket($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('message', $responseContent);
    }

    /**
     * @test
     * @dataProvider invalidRequestDataProvider
     */
    public function post_tickets_should_respond_400_when_authorid_or_message_are_missing($request)
    {
        $this->ticketRepositoryMock
            ->expects($this->never())
            ->method('insert');

        $response = $this->ticketsController->postTicket($request);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseContent);
    }

    public function invalidRequestDataProvider()
    {
	    $requestWithNoAuthorId = Request::create('/tickets', 'POST', [], [], [], [], json_encode([
		    'message' => 'test message'
	    ]));

	    $requestWithNoMessage = Request::create('/tickets', 'POST', [], [], [], [], json_encode([
		    'authorId' => 'd7a8cd93-7df7-48be-a999-244e8e2f62d8'
	    ]));

        return [
	        [$requestWithNoAuthorId],
	        [$requestWithNoMessage]
        ];
    }
}
