<?php

namespace SimpleTicketing\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Controller\TicketsController;
use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketId;
use SimpleTicketing\Ticket\TicketMessage;
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

	/** @test */
	public function get_tickets_should_respond_200()
	{
		$userId = new UserId('4d8f38dc-05d4-42a6-93fe-69a72fc533b1');
		$this->ticketRepositoryMock
			->expects($this->once())
			->method('findByUserId')
			->with($userId)
			->willReturn([Ticket::createWithAuthorIdAndMessage($userId, new TicketMessage('test'))]);

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

		$request = Request::create('/tickets', 'GET');

		$response = $this->ticketsController->getTickets($request);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}

	/** @test */
	public function get_tickets_id_should_respond_200()
	{
		$userId = new UserId('4d8f38dc-05d4-42a6-93fe-69a72fc533b1');
		$ticket = Ticket::createWithAuthorIdAndMessage($userId, new TicketMessage('test'));

		$this->ticketRepositoryMock
			->expects($this->once())
			->method('findById')
			->with($ticket->id())
			->willReturn($ticket);

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

		$request = Request::create("/tickets", 'GET');
		$request->attributes->set('id', $ticket->id());

		$response = $this->ticketsController->getTicketById($request);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}

	/** @test */
	public function get_tickets_id_should_respond_400_when_ticket_id_is_malformed()
	{
		$this->ticketRepositoryMock
			->expects($this->never())
			->method('findById');

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

		$request = Request::create("/tickets", 'GET');
		$request->attributes->set('id', 'bad_id');

		$response = $this->ticketsController->getTicketById($request);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
	}

	/** @test */
	public function get_tickets_id_should_respond_403_when_ticket_doesnt_belong_to_user()
	{
		$userId = new UserId('4d8f38dc-05d4-42a6-93fe-69a72fc533c3');
		$ticket = Ticket::createWithAuthorIdAndMessage($userId, new TicketMessage('test'));

		$this->ticketRepositoryMock
			->expects($this->once())
			->method('findById')
			->with($ticket->id())
			->willReturn($ticket);

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

		$request = Request::create("/tickets", 'GET');
		$request->attributes->set('id', $ticket->id());

		$response = $this->ticketsController->getTicketById($request);

		$this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
	}

	/** @test */
	public function get_tickets_id_should_respond_404_when_ticket_is_not_found()
	{
		$ticketId = new TicketId();
		$this->ticketRepositoryMock
			->expects($this->once())
			->method('findById')
			->with($ticketId)
			->willReturn(null);

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

		$request = Request::create("/tickets", 'GET');
		$request->attributes->set('id', $ticketId);

		$response = $this->ticketsController->getTicketById($request);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
	}
}
