<?php

namespace TicketSwap\Assessment;

use Ramsey\Uuid\Uuid;

final class ListingId implements \Stringable
{
    public function __construct(private string $id)
    {
    }

    public function __toString() : string
    {
        return $this->id;
    }

    public static function generateRandom(): self
    {
        return new self(Uuid::uuid4()->toString());
    }
}
