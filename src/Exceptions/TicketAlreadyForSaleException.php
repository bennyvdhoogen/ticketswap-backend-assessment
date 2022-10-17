<?php

namespace TicketSwap\Assessment\Exceptions;

class TicketAlreadyForSaleException extends \Exception
{
    public const ALREADY_FOR_SALE = "This listing contains a barcode that is already for sale";

    public function __construct()
    {
        //
    }
}