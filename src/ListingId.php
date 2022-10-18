<?php

namespace TicketSwap\Assessment;

use Ramsey\Uuid\Uuid;
use TicketSwap\Assessment\Traits\Id;

final class ListingId implements \Stringable
{
    use Id;

    public function __construct(private string $id)
    {
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
