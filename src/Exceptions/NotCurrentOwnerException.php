<?php

namespace TicketSwap\Assessment\Exceptions;

class NotCurrentOwnerException extends \Exception
{
    public const NOT_CURRENT_OWNER = "This listing contains a barcode that was last bought by someone else";

    public function __construct()
    {
        //
    }
}