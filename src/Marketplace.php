<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;

final class Marketplace
{
    /**
     * @param array<Listing> $listingsForSale
     */
    public function __construct(private array $listingsForSale = [])
    {
    }

    /**
     * @return array<Listing>
     */
    public function getListingsForSale() : array
    {
        return $this->listingsForSale;
    }

    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listingsForSale as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId)) {

                    if ($ticket->isBought()) {
                        throw new TicketAlreadySoldException("The given ticket has already been sold");
                    }

                   return $ticket->buyTicket($buyer);
                }
            }
        }
    }

    // TODO: add validation/business rules for adding listings
    public function setListingForSale(Listing $listing) : void
    {
        $this->listingsForSale[] = $listing;
    }
}
