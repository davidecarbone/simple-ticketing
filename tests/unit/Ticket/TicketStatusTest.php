<?php

namespace SimpleTicketing\Tests\Unit\Ticket;

use PHPUnit\Framework\TestCase;
use SimpleTicketing\Ticket\TicketStatus;
use SimpleTicketing\Ticket\TicketStatusException;

class TicketStatusTest extends TestCase
{
    /** @test */
    public function can_be_built_with_statuses_new_assigned_or_closed()
    {
        $statusNew = new TicketStatus(TicketStatus::NEW);
        $statusAssigned = new TicketStatus(TicketStatus::ASSIGNED);
        $statusClosed = new TicketStatus(TicketStatus::CLOSED);

        $this->assertInstanceOf(TicketStatus::class, $statusNew);
        $this->assertInstanceOf(TicketStatus::class, $statusAssigned);
        $this->assertInstanceOf(TicketStatus::class, $statusClosed);
    }

	/** @test */
	public function throws_exception_when_status_is_not_valid()
	{
		$this->expectException(TicketStatusException::class);

		$admin = new TicketStatus('UNKNOWN');
	}
}
