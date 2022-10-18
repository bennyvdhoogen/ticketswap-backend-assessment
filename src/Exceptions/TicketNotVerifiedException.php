<?php

namespace TicketSwap\Assessment\Exceptions;

class TicketNotVerifiedException extends \Exception
{
    public const NOT_VERIFIED = "This ticket has not been verified by an administrator";

    public function __construct()
    {
        //
    }
}
