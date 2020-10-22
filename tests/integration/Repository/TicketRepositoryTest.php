<?php

namespace SimpleTicketing\Tests\Integration\Repository;

use SimpleTicketing\Tests\ContainerAwareTest;
use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketId;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\User\UserId;

class TicketRepositoryTest extends ContainerAwareTest
{
    /** @var TicketRepository */
    private $repository;

    /** @var TicketId */
	private $ticketId;

	public function setUp()
    {
        parent::setUp();

        $this->repository = self::$container->get(TicketRepository::class);
    }

    public function tearDown(): void
    {
	    parent::tearDown();

	    $this->repository->deleteById($this->ticketId);
    }

	/** @test */
    public function can_insert_tickets_and_find_them_by_id()
    {
    	$authorId = new UserId();
    	$ticket = Ticket::createWithMessage(new TicketMessage('test message', $authorId));

        $this->ticketId = $this->repository->insert($ticket);

        $insertedTicket = $this->repository->findById($this->ticketId);
        $ticketData = $insertedTicket->toArray();

	    $this->assertIsString($ticketData['id']);
	    $this->assertNull($ticketData['assignedTo']);
	    $this->assertEquals('Nuovo', $ticketData['status']);
	    $this->assertIsArray($ticketData['messages']);
	    $this->assertEquals('test message', $ticketData['messages'][0]['body']);
	    $this->assertEquals($authorId, $ticketData['messages'][0]['authorId']);
	    $this->assertIsString($ticketData['createdOn']);
	    $this->assertIsString($ticketData['updatedOn']);
    }

	/** @test */
	public function can_find_tickets_by_user_id()
	{
		$authorId = new UserId();
		$ticket = Ticket::createWithMessage(new TicketMessage('test message 2', $authorId));

		$this->ticketId = $this->repository->insert($ticket);
		$tickets = $this->repository->findByUserId($authorId);

		$ticketData = $tickets[0]->toArray();

		$this->assertIsString($ticketData['id']);
		$this->assertNull($ticketData['assignedTo']);
		$this->assertEquals('Nuovo', $ticketData['status']);
		$this->assertIsArray($ticketData['messages']);
		$this->assertEquals('test message 2', $ticketData['messages'][0]['body']);
		$this->assertEquals($authorId, $ticketData['messages'][0]['authorId']);
		$this->assertIsString($ticketData['createdOn']);
		$this->assertIsString($ticketData['updatedOn']);
	}
}
