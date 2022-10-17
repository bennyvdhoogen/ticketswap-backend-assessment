<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Traits\Id;

final class TicketId implements \Stringable
{
    use Id;

    public function __construct(private string $id)
    {
    }

    public function __toString() : string
    {
        return $this->id;
    }

    public function equals(TicketId $otherId) : bool
    {
        return $this->id === $otherId->id;
    }
}
