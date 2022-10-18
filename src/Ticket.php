<?php

namespace TicketSwap\Assessment;

final class Ticket
{
    private ?string $bought_at = null;

    /**
     * @param array<Barcode> $barcode
     */
    public function __construct(private array $barcodes, private ?Buyer $buyer = null)
    {
        $this->id = TicketId::generateRandom();
    }

    public function getId(): TicketId
    {
        return $this->id;
    }

    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    public function getBuyer(): Buyer
    {
        return $this->buyer;
    }

    public function isBought(): bool
    {
        return $this->buyer !== null;
    }

    public function buyTicket(Buyer $buyer): self
    {
        $this->buyer = $buyer;
        $this->bought_at = date('Y-m-d H:i:s');

        return $this;
    }
}
