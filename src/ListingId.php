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
        $uuid = Uuid::uuid4();
        return new self($uuid);
    }
}
