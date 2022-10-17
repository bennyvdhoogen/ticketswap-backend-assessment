<?php

namespace TicketSwap\Assessment;

use Money\Money;
use TicketSwap\Assessment\Exceptions\ListingContainsDuplicateBarcodeException;

final class Listing
{
    private ListingId $id;
    private bool $isVerified = false;

    /**
     * @param array<Ticket> $tickets
     */
    public function __construct(
        private Seller $seller,
        private array $tickets,
        private Money $price
    ) {
        $this->id = ListingId::generateRandom();

        // Check for barcode collision inside listing
        $barcodeStrings = [];
        foreach ($this->tickets as $ticket) {
            foreach ($ticket->getBarcodes() as $barcode) {
                if (in_array((string) $barcode, $barcodeStrings)) {
                    throw new ListingContainsDuplicateBarcodeException(ListingContainsDuplicateBarcodeException::CONTAINS_DUPLICATE_BARCODES);
                }

                $barcodeStrings[] = (string) $barcode;
            }
        }
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

    public function isVerified() : bool
    {
        return $this->isVerified;
    }

    public function verify(Admin $admin)
    {
        $this->isVerified = true;
    }
}
