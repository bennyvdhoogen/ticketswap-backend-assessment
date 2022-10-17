<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Exceptions\NotCurrentOwnerException;
use TicketSwap\Assessment\Exceptions\TicketAlreadyForSaleException;
use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;

final class Marketplace
{
    /**
     * @param array<Listing> $listings
     */
    public function __construct(private array $listings = [])
    {
    }

    /**
     * @return array<Listing>
     */
    public function getListingsForSale() : array
    {
        $listingsForSale = [];
        foreach ($this->listings as $listing) {
            $soldTicketsInListing = 0;

            foreach ($listing->getTickets() as $ticket) {
                if ($ticket->isBought()) {
                    $soldTicketsInListing++;
                }
            }

            if (count($listing->getTickets()) != $soldTicketsInListing) {
                $listingsForSale[] = $listing;
            }
        }

        return $listingsForSale;
    }

    public function containsActiveListingWithBarcode(Barcode $barcode) : bool
    {
        foreach ($this->listings as $listing) {
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
        foreach($this->listings as $listing) {
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

            $ticketsWithSameBarcode = $this->getTicketsByBarcode($ticket->getBarcode());
            if (!empty($ticketsWithSameBarcode)) {
                if ((string) $ticketsWithSameBarcode[0]->getBuyer() !== (string) $listing->getSeller()) {
                    throw new NotCurrentOwnerException(NotCurrentOwnerException::NOT_CURRENT_OWNER);
                }
            }
        }

        $this->listings[] = $listing;
    }

    public function getTicketsByBarcode(Barcode $barcode) : array
    {
        $tickets = [];

        foreach ($this->listings as $listing) {
            foreach ($listing->getTickets() as $ticket) {
                ray($ticket->getBarcode());
                ray($barcode);
                if ((string) $ticket->getBarcode() === (string) $barcode) {
                    $tickets[] = $ticket;
                }
            }
        }

        // Sort the tickets by the bought_at date, descending
        usort($tickets, function($first,$second) {
            return $first->bought_at < $second->bought_at;
        });

        return $tickets;
    }
}
