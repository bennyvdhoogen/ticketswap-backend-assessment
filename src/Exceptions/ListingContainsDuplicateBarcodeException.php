<?php

namespace TicketSwap\Assessment\Exceptions;

class ListingContainsDuplicateBarcodeException extends \Exception
{
    public const CONTAINS_DUPLICATE_BARCODES = "This listing contains the same barcode mulitple times";

    public function __construct()
    {
        //
    }
}