<?php

namespace TicketSwap\Assessment\Factories;

use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Buyer;
use TicketSwap\Assessment\Ticket;


/**
 * Very simple factory class to quickly mock up some instantiated records for testing
 */
class TicketFactory
{
    public static function availableTicketWithBarcode($ean13Code)
    {
        return new Ticket(
            [new Barcode('EAN-13', $ean13Code)]
        );
    }

    public static function boughtTicketWithBarcode($ean13Code, $buyerName)
    {
        return new Ticket(
            [new Barcode('EAN-13', $ean13Code)],
            new Buyer($buyerName),
        );
    }
}
