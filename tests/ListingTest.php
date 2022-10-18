<?php

namespace TicketSwap\Assessment\tests;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Admin;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Buyer;
use TicketSwap\Assessment\Exceptions\ListingContainsDuplicateBarcodeException;
use TicketSwap\Assessment\Factories\TicketFactory;
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
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
                new Ticket(
                    [new Barcode('EAN-13', '48974312924')]
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        $this->assertCount(2, $listing->getTickets());
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_create_a_listing_with_duplicate_barcodes()
    {
        $this->expectException(ListingContainsDuplicateBarcodeException::class);

        $listing = new Listing(
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        $this->assertCount(0, $listing->getTickets());
    }

    /**
     * @test
     */
    public function it_should_list_the_tickets_for_sale()
    {
        $soldTicketWithBarcode = TicketFactory::soldTicketWithBarcode('28774312924', 'Sarah');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('38957953498');

        $listing = new Listing(
            tickets: [
                $soldTicketWithBarcode,
                $availableTicket
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        $ticketsForSale = $listing->getTickets(true);

        $this->assertCount(1, $ticketsForSale);
        $this->assertSame((string) $availableTicket->getId(), (string) $ticketsForSale[0]->getId());
    }

    /**
     * @test
     */
    public function it_should_list_the_tickets_not_for_sale()
    {
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('38957953498');
        $boughtTicket = TicketFactory::soldTicketWithBarcode('28957953497', 'Sarah');
        $listing = new Listing(
            tickets: [
                $availableTicket,
                $boughtTicket
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );


        $ticketsNotForSale = $listing->getTickets(false);

        $this->assertCount(1, $ticketsNotForSale);
        $this->assertSame((string) $boughtTicket->getId(), (string) $ticketsNotForSale[0]->getId());
    }

    public function it_should_be_possible_to_verify_a_listing_for_an_admin()
    {
        $admin = new Admin('Administrator');
        $listing = new Listing(
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
            seller: new Seller('Pascal'),
        );

        $listing->verify($admin);

        $this->assertSame(true, $listing->isVerified());
    }
}
