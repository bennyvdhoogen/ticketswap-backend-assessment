<?php

namespace TicketSwap\Assessment\Exceptions;

class TicketAlreadySoldException extends \Exception
{
    public const ALREADY_SOLD = "The given ticket has already been sold";

    public function __construct()
    {
        //
    }
}
