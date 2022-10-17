<?php

namespace TicketSwap\Assessment;

use Money\Money;

final class Listing
{
    private ListingId $id;

    /**
     * @param array<Ticket> $tickets
     */
    public function __construct(
        private Seller $seller,
        private array $tickets,
        private Money $price
    ) {
        $this->id = ListingId::generateRandom();
    }

    public function getId() : ListingId
    {
        return $this->id;
    }

    public function getSeller() : Seller
    {
        return $this->seller;
    }

    /**
     * @return array<Ticket>
     */
    public function getTickets(?bool $forSale = null) : array
    {
        if (true === $forSale) {
            $forSaleTickets = [];
            foreach ($this->tickets as $ticket) {
                if (!$ticket->isBought()) {
                    $forSaleTickets[] = $ticket;
                }
            }

            return $forSaleTickets;
        } else if (false === $forSale) {
            $notForSaleTickets = [];
            foreach ($this->tickets as $ticket) {
                if ($ticket->isBought()) {
                    $notForSaleTickets[] = $ticket;
                }
            }

            return $notForSaleTickets;
        } else {
            return $this->tickets;
        }
    }

    public function getPrice() : Money
    {
        return $this->price;
    }
}
