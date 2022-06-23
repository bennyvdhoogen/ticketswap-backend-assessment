<?php

namespace TicketSwap\Assessment;

final class TicketAlreadySoldException extends \Exception
{
    public static function withTicket(Ticket $ticket) : self
    {
        return new self(
            sprintf(
                'Ticket (%s) has already been sold',
                (string) $ticket->getId()
            )
        );
    }
}
