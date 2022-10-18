<?php

namespace TicketSwap\Assessment\tests;

use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Ticket;

class TicketTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_possible_to_create_a_ticket_with_multiple_barcodes()
    {
        $ticket = new Ticket(
            [
                new Barcode('EAN-13', '38974312923'),
                new Barcode('EAN-13', '58974312924')
            ]
        );

        $this->assertCount(2, $ticket->getBarcodes());
    }
}
