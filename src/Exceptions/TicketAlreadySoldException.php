<?php

namespace TicketSwap\Assessment\Exceptions;

class TicketAlreadySoldException extends \Exception
{
    public function __construct()
    {
        //
    }

    public static function throw(): self
    {
        return new self(
            "The given ticket has already been sold"
        );
    }
}