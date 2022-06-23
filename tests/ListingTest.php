<?php

namespace TicketSwap\Assessment\tests;

use PHPUnit\Framework\TestCase;
use Money\Currency;
use Money\Money;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Buyer;
use TicketSwap\Assessment\Listing;
use TicketSwap\Assessment\ListingId;
use TicketSwap\Assessment\Seller;
use TicketSwap\Assessment\Ticket;
use TicketSwap\Assessment\TicketId;

class ListingTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_possible_to_create_a_listing()
    {
        $listing = new Listing(
            id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
            tickets: [
                new Ticket(
                    new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    new Barcode('EAN-13', '38974312923')
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        $this->assertCount(1, $listing->getTickets());
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_create_a_listing_with_duplicate_barcodes()
    {
        $this->markTestSkipped('Needs to be implemented');
    }

    /**
     * @test
     */
    public function it_should_list_the_tickets_for_sale()
    {
        $listing = new Listing(
            id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
            tickets: [
                new Ticket(
                    new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    new Barcode('EAN-13', '38974312923'),
                    new Buyer('Jane')
                ),
                new Ticket(
                    new TicketId('B47CBE2D-9F80-47D9-A9CC-894CE82AA6BA'),
                    new Barcode('EAN-13', '38957953498')
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        $ticketsForSale = $listing->getTickets(true);

        $this->assertCount(1, $ticketsForSale);
        $this->assertSame('B47CBE2D-9F80-47D9-A9CC-894CE82AA6BA', (string) $ticketsForSale[0]->getId());
    }

        /**
     * @test
     */
    public function it_should_list_the_tickets_not_for_sale()
    {
        $listing = new Listing(
            id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
            tickets: [
                new Ticket(
                    new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                    new Barcode('EAN-13', '38974312923'),
                    new Buyer('Jane')
                ),
                new Ticket(
                    new TicketId('B47CBE2D-9F80-47D9-A9CC-894CE82AA6BA'),
                    new Barcode('EAN-13', '38957953498')
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        
        $ticketsNotForSale = $listing->getTickets(false);

        $this->assertCount(1, $ticketsNotForSale);
        $this->assertSame('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B', (string) $ticketsNotForSale[0]->getId());
    }
}
