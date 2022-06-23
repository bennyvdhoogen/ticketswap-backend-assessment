<?php

namespace TicketSwap\Assessment;

final class Buyer implements \Stringable
{
    public function __construct(private string $name)
    {
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
