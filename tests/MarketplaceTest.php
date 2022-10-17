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
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923'),
                            new Buyer('Sarah'),
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923')
                        ),
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
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923')
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertNotNull($boughtTicket);
        $this->assertSame('EAN-13:38974312923', (string) $boughtTicket->getBarcode());
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_the_same_ticket_twice()
    {
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923')
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $this->expectException(TicketAlreadySoldException::class);

        $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $marketplace->buyTicket(
            buyer: new Buyer('William'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
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
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923')
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
                        new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                        new Barcode('EAN-13', '893759834')
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
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            new Barcode('EAN-13', '38974312923')
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
                        new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                        new Barcode('EAN-13', '38974312923')
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
        $ticketId = new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            $ticketId,
                            new Barcode('EAN-13', '38974312923')
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $ticketId
        );

        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Sarah'),
                tickets: [
                    new Ticket(
                        $boughtTicket->getId(),
                        new Barcode('EAN-13', '38974312923')
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
        $ticketId = new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            $ticketId,
                            new Barcode('EAN-13', '38974312923')
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $ticketId
        );

        $this->expectException(NotCurrentOwnerException::class);

        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Pascal'),
                tickets: [
                    new Ticket(
                        $boughtTicket->getId(),
                        new Barcode('EAN-13', '38974312923')
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
        $ticketId = new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B');
        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            $ticketId,
                            new Barcode('EAN-13', '38974312923')
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $ticketId
        );

        $marketplace->setListingForSale(
            new Listing(
                seller: new Seller('Sarah'),
                tickets: [
                    new Ticket(
                        $boughtTicket->getId(),
                        new Barcode('EAN-13', '38974312923')
                    ),
                ],
                price: new Money(5950, new Currency('EUR')),
            )
        );

        $otherBoughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Pascal'),
            ticketId: $ticketId
        );
    }
}
