<?php

namespace TicketSwap\Assessment;

final class Ticket
{
    public function __construct(private TicketId $id, private Barcode $barcode, private ?Buyer $buyer = null)
    {
    }

    public function getId() : TicketId
    {
        return $this->id;
    }

    public function getBarcode() : Barcode
    {
        return $this->barcode;
    }

    public function getBuyer() : Buyer
    {
        return $this->buyer;
    }

    public function isBought() : bool
    {
        return $this->buyer !== null;
    }

    public function buyTicket(Buyer $buyer) : self
    {
        $this->buyer = $buyer;

        return $this;
    }
}
