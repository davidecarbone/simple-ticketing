<?php

namespace SimpleTicketing\Tests\Unit\Ticket;

use PHPUnit\Framework\TestCase;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketId;

class TicketTest extends TestCase
{
    /** @test */
    public function can_be_built_with_author_and_exported_to_array()
    {
        $ticket = Ticket::createWithAuthorId('test123');
        $ticketData = $ticket->toArray();

        $this->assertInstanceOf(TicketId::class, $ticketData['id']);
        $this->assertEquals('test123', $ticketData['authorId']);
        $this->assertNull($ticketData['assignedTo']);
        $this->assertEquals('Nuovo', $ticketData['status']);
        $this->assertIsString($ticketData['createdOn']);
        $this->assertIsString($ticketData['updatedOn']);
    }
}
