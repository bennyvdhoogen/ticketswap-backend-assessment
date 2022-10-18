<?php

namespace TicketSwap\Assessment\tests;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Admin;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Buyer;
use TicketSwap\Assessment\Exceptions\NotCurrentOwnerException;
use TicketSwap\Assessment\Exceptions\TicketAlreadyForSaleException;
use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;
use TicketSwap\Assessment\Exceptions\TicketNotVerifiedException;
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
    public function it_should_list_all_verified_tickets_for_sale()
    {
        $soldTicketWithBarcode = TicketFactory::soldTicketWithBarcode('883749835', 'Sarah');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('893759834');

        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $soldTicketWithBarcode
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

        // Simulate that these listings are verified by an admin
        $admin = new Admin('Administrator');
        $unverifiedListings = $marketplace->getUnverifiedListings();
        foreach ($unverifiedListings as $listing) {
            $listing->verify($admin);
        }

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(1, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_not_show_listings_where_all_tickets_are_sold()
    {
        $soldTicketWithBarcode = TicketFactory::soldTicketWithBarcode('883749835', 'Sarah');
        $soldTicketWithBarcode2 = TicketFactory::soldTicketWithBarcode('783749833', 'Tom');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('893759834');

        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Francis'),
                    tickets: [
                        $soldTicketWithBarcode
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $soldTicketWithBarcode2
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        // Simulate that these listings are verified by an admin
        $admin = new Admin('Administrator');
        $unverifiedListings = $marketplace->getUnverifiedListings();
        foreach ($unverifiedListings as $listing) {
            $listing->verify($admin);
        }

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(0, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_not_list_unverified_tickets_for_sale()
    {
        $soldTicketWithBarcode = TicketFactory::soldTicketWithBarcode('883749835', 'Sarah');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('893759834');

        $marketplace = new Marketplace(
            listings: [
                new Listing(
                    seller: new Seller('Pascal'),
                    tickets: [
                        $soldTicketWithBarcode
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

        $this->assertCount(0, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_buy_a_verified_ticket()
    {
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('893759834');
        $newListing = new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                $availableTicket
            ],
            price: new Money(4950, new Currency('EUR')),
        );
        $admin = new Admin('Administrator');
        $newListing->verify($admin);
        $marketplace = new Marketplace(
            listings: [
                $newListing
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
    public function it_should_not_be_possible_to_buy_an_unverified_ticket()
    {
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('893759834');
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

        $this->expectException(TicketNotVerifiedException::class);

        $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_the_same_ticket_twice()
    {
        $admin = new Admin('Administrator');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('38974312923');
        $listing1 = (new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                $availableTicket
            ],
            price: new Money(4950, new Currency('EUR')),
        ));
        $marketplace = new Marketplace(
            listings: [
                $listing1
            ]
        );
        $listing1->verify($admin);

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
    public function it_should_be_possible_to_put_a_listing_up_for_verification()
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

        $listingsForSale = $marketplace->getUnverifiedListings();

        $this->assertCount(2, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_put_a_listing_for_sale()
    {
        $admin = new Admin('Administrator');
        $listing1 = new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
        );
        $marketplace = new Marketplace(
            listings: [
                $listing1
            ]
        );
        $listing2 = new Listing(
            seller: new Seller('Tom'),
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '18974412925')]
                ),
            ],
            price: new Money(4950, new Currency('EUR')),
        );
        $marketplace->setListingForSale(
            $listing2
        );
        $listing2->verify($admin);

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(1, $listingsForSale);
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
        $admin = new Admin('Administrator');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('38974312923');
        $listing1 = new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                $availableTicket
            ],
            price: new Money(4950, new Currency('EUR')),
        );
        $marketplace = new Marketplace(
            listings: [
               $listing1
            ]
        );
        $listing1->verify($admin);

        $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $listing2 = new Listing(
            seller: new Seller('Sarah'),
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
            ],
            price: new Money(5950, new Currency('EUR')),
        );
        $listing2->verify($admin);

        $marketplace->setListingForSale(
            $listing2
        );

        $listingsForSale = $marketplace->getListingsForSale();
        $this->assertCount(1, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_for_someone_other_than_the_last_buyer_to_sell_it_again()
    {
        $admin = new Admin('Administrator');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('38974312923');
        $listing1 = new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                $availableTicket
            ],
            price: new Money(4950, new Currency('EUR')),
        );
        $marketplace = new Marketplace(
            listings: [
                $listing1
            ]
        );
        $listing1->verify($admin);

        $soldTicketWithBarcode = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $this->expectException(NotCurrentOwnerException::class);
        $listing2 = new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                new Ticket(
                    [new Barcode('EAN-13', '38974312923')]
                ),
            ],
            price: new Money(5950, new Currency('EUR')),
        );

        $marketplace->setListingForSale(
            $listing2
        );

        $listing2->verify($admin);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_sell_tickets_back_and_forth()
    {
        $admin = new Admin('Administrator');
        $availableTicket = TicketFactory::unsoldTicketWithBarcode('38974312923');
        $listing1 = new Listing(
            seller: new Seller('Pascal'),
            tickets: [
                $availableTicket
            ],
            price: new Money(4950, new Currency('EUR')),
        );
        $marketplace = new Marketplace(
            listings: [
                $listing1
            ]
        );
        $listing1->verify($admin);

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: $availableTicket->getId()
        );

        $readdedTicket = new Ticket(
            [new Barcode('EAN-13', '38974312923')]
        );
        $listing2 = new Listing(
            seller: new Seller('Sarah'),
            tickets: [
                $readdedTicket
            ],
            price: new Money(5950, new Currency('EUR')),
        );
        $listing2->verify($admin);
        $marketplace->setListingForSale(
            $listing2
        );

        $otherBoughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Pascal'),
            ticketId: $readdedTicket->getId()
        );

        $this->assertSame('Pascal', (string) $otherBoughtTicket->getBuyer());
    }
}
