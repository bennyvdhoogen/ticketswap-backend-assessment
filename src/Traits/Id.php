<?php

namespace TicketSwap\Assessment\Traits;

use Ramsey\Uuid\Uuid;

trait id
{
    public static function generateRandom(): self
    {
        return new self(Uuid::uuid4()->toString());
    }
}
