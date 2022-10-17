<?php

namespace TicketSwap\Assessment\Exceptions;

class ListingContainsDuplicateBarcodeException extends \Exception
{
    public function __construct()
    {
        //
    }

    public static function throw(): self
    {
        return new self(
            "This listing contains the same barcode mulitple times"
        );
    }
}