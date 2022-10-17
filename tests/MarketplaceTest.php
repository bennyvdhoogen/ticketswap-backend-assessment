<?php

namespace TicketSwap\Assessment\tests;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Buyer;
use TicketSwap\Assessment\Exceptions\NotCurrentOwnerException;
use TicketSwap\Assessment\Exceptions\TicketAlreadyForSaleException;
use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;
use TicketSwap\Assessment\Factories\TicketFactory;
use TicketSwap\Assessment\Listing;
use TicketSwap\Assessment\ListingId;
use TicketSwap\Assessment\Marketplace;
use TicketSwap\Assessment\Seller;
use TicketSwap\Assessment\Ticket;
use TicketSwap\Assessment\TicketId;

class MarketplaceTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_list_all_the_tickets_for_sale()
    {
        $boughtTicketWithBarcode = TicketFactory::boughtTicketWithBarcode('883749835', 'Sarah');
        $availableTicket = TicketFactory::availableTicketWithBarcode('893759834');

        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $boughtTicketWithBarcode
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $availableTicket
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(1, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_buy_a_ticket()
    {
        $availableTicket = TicketFactory::availableTicketWithBarcode('893759834');

        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $availableTicket
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $this->assertNotNull($boughtTicket);
        $this->assertSame('EAN-13:893759834', (string) $boughtTicket->getBarcodes()[0]);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_the_same_ticket_twice()
    {
        $availableTicket = TicketFactory::availableTicketWithBarcode('38974312923');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $availableTicket
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $this->expectException(TicketAlreadySoldException::class);

        $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $marketplace->buyTicket(
            buyer: new Buyer('William'),
            ticketId: $availableTicket->getId()
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_put_a_listing_for_sale()
    {
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            [new Barcode('EAN-13', '38974312923')]
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Tom'),
                tickets: [
                    new Ticket(
                        [new Barcode('EAN-13', '18974412925')]
                    ),
                ],
                price: new Money(4950, new Currency('EUR')),
            )
        );

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(2, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_sell_a_ticket_with_a_barcode_that_is_already_for_sale()
    {
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            [new Barcode('EAN-13', '38974312923')]
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $this->expectException(TicketAlreadyForSaleException::class);
        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Tom'),
                tickets: [
                    new Ticket(
                        [new Barcode('EAN-13', '38974312923')]
                    ),
                ],
                price: new Money(5950, new Currency('EUR')),
            )
        );

        $listingsForSale = $marketplace->getListingsForSale();
        $this->assertCount(1, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_be_possible_for_a_buyer_of_a_ticket_to_sell_it_again()
    {
        $availableTicket = TicketFactory::availableTicketWithBarcode('38974312923');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $availableTicket
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Sarah'),
                tickets: [
                    new Ticket(
                        [new Barcode('EAN-13', '38974312923')]
                    ),
                ],
                price: new Money(5950, new Currency('EUR')),
            )
        );

        $listingsForSale = $marketplace->getListingsForSale();
        $this->assertCount(1, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_for_someone_other_than_the_last_buyer_to_sell_it_again()
    {
        $availableTicket = TicketFactory::availableTicketWithBarcode('38974312923');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $availableTicket
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicketWithBarcode = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $this->expectException(NotCurrentOwnerException::class);

        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Pascal'),
                tickets: [
                    new Ticket(
                        [new Barcode('EAN-13', '38974312923')]
                    ),
                ],
                price: new Money(5950, new Currency('EUR')),
            )
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_sell_tickets_back_and_forth()
    {
        $availableTicket = TicketFactory::availableTicketWithBarcode('38974312923');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $availableTicket
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $readdedTicket = new Ticket(
            [new Barcode('EAN-13', '38974312923')]
        );
        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Sarah'),
                tickets: [
                    $readdedTicket
                ],
                price: new Money(5950, new Currency('EUR')),
            )
        );

        $otherBoughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Pascal'),
            ticketId: $readdedTicket->getId()
        );

        $this->assertSame('Pascal', (string) $otherBoughtTicket->getBuyer());
    }
}
