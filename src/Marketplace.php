<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Exceptions\TicketAlreadyForSaleException;
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

    public function containsActiveListingWithBarcode(Barcode $barcode) : bool
    {
        foreach ($this->listingsForSale as $listing) {
            foreach ($listing->getTickets() as $ticket) {
                if ((string) $ticket->getBarcode() === (string) $barcode) {
                    if (!$ticket->isBought()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listingsForSale as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId)) {

                    if ($ticket->isBought()) {
                        throw new TicketAlreadySoldException(TicketAlreadySoldException::ALREADY_SOLD);
                    }

                   return $ticket->buyTicket($buyer);
                }
            }
        }
    }

    public function setListingForSale(Listing $listing) : void
    {
        foreach ($listing->getTickets() as $ticket) {
            if ($this->containsActiveListingWithBarcode($ticket->getBarcode())) {
                throw new TicketAlreadyForSaleException(TicketAlreadyForSaleException::ALREADY_FOR_SALE);
            }
        }

        $this->listingsForSale[] = $listing;
    }
}
