<?php

namespace TicketSwap\Assessment;

final class Barcode implements \Stringable
{
    public function __construct(private string $type, private string $value)
    {
    }

    public function __toString() : string
    {
        return sprintf('%s:%s', $this->type, $this->value);
    }
}
