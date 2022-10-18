<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Exceptions\NotCurrentOwnerException;
use TicketSwap\Assessment\Exceptions\TicketAlreadyForSaleException;
use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;
use TicketSwap\Assessment\Exceptions\TicketNotVerifiedException;

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
    public function getListingsForSale($includeUnverified = false) : array
    {
        $listingsForSale = [];
        foreach ($this->listings as $listing) {
            $soldTicketsInListing = 0;

            if ($includeUnverified === false) {
                if ($listing->isVerified() === false) {
                    continue;
                }
            }

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

    public function containsActiveListingWithBarcode(Barcode $listingBarcode) : bool
    {
        foreach ($this->listings as $listing) {
            foreach ($listing->getTickets() as $ticket) {
                foreach ($ticket->getBarcodes() as $barcode) {
                    if ((string) $barcode === (string) $listingBarcode) {
                        if (!$ticket->isBought()) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listings as $listing) {
            ray($listing);
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId)) {

                    if ($ticket->isBought()) {
                        throw new TicketAlreadySoldException(TicketAlreadySoldException::ALREADY_SOLD);
                    }

                    if ($listing->isVerified() === false) {
                        throw new TicketNotVerifiedException(TicketNotVerifiedException::NOT_VERIFIED);
                    }

                   return $ticket->buyTicket($buyer);
                }
            }
        }
    }

    public function setListingForSale(Listing $listing) : void
    {
        foreach ($listing->getTickets() as $ticket) {
            foreach ($ticket->getBarcodes() as $barcode) {
                if ($this->containsActiveListingWithBarcode($barcode)) {
                    throw new TicketAlreadyForSaleException(TicketAlreadyForSaleException::ALREADY_FOR_SALE);
                }

                $ticketsWithSameBarcode = $this->getTicketsByBarcode($barcode);
                if (!empty($ticketsWithSameBarcode)) {
                    if ((string) $ticketsWithSameBarcode[0]->getBuyer() !== (string) $listing->getSeller()) {
                        throw new NotCurrentOwnerException(NotCurrentOwnerException::NOT_CURRENT_OWNER);
                    }
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
                foreach ($ticket->getBarcodes() as $ticketBarcode) {
                    if ((string) $ticketBarcode === (string) $barcode) {
                        $tickets[] = $ticket;
                    }
                }
            }
        }

        // Sort the tickets by the bought_at date, descending
        usort($tickets, function($first,$second) {
            return $first->bought_at < $second->bought_at;
        });

        return $tickets;
    }

    public function getUnverifiedListings()
    {
        return $this->getListingsForSale(true);
    }
}
