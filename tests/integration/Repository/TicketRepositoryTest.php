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
    public function can_insert_tickets()
    {
    	$authorId = new UserId();
	    $message = new TicketMessage('test message');
	    $ticket = Ticket::createWithAuthorIdAndMessage($authorId, $message);

        $this->ticketId = $this->repository->insert($ticket);

        $insertedTicket = $this->repository->findById($this->ticketId);
        $ticketData = $insertedTicket->toArray();

	    $this->assertInstanceOf(TicketId::class, $ticketData['id']);
	    $this->assertEquals($authorId, $ticketData['authorId']);
	    $this->assertNull($ticketData['assignedTo']);
	    $this->assertEquals('Nuovo', $ticketData['status']);
	    $this->assertIsArray($ticketData['messages']);
	    //$this->assertEquals('test message', $ticketData['messages'][0]);
	    $this->assertIsString($ticketData['createdOn']);
	    $this->assertIsString($ticketData['updatedOn']);
    }
}
