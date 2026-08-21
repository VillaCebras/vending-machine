<?php

namespace Infrastructure\Doctrine;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Domain\Model\VendingMachine;
use Domain\Model\VendingMachineSnapshot;

#[ORM\Entity]
#[ORM\Table(name: 'vending_machine')]
class VendingMachineRecord
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private bool $maintenance = false;

    #[ORM\Column(nullable: true)]
    private ?string $customerId = null;

    /** @var list<int> */
    #[ORM\Column(type: Types::JSON)]
    private array $insertedCoins = [];

    /** @var list<int> */
    #[ORM\Column(type: Types::JSON)]
    private array $changeCoins = [];

    /** @var array<string, int> */
    #[ORM\Column(type: Types::JSON)]
    private array $stock = [];

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function syncFrom(VendingMachine $machine): void
    {
        $snapshot = $machine->snapshot();
        $this->maintenance = $snapshot->maintenance;
        $this->customerId = $snapshot->customerId;
        $this->insertedCoins = $snapshot->insertedCoinCents;
        $this->changeCoins = $snapshot->changeCoinCents;
        $this->stock = $snapshot->stock;
    }

    public function toDomain(): VendingMachine
    {
        return VendingMachine::fromSnapshot(new VendingMachineSnapshot(
            $this->maintenance,
            $this->customerId,
            $this->insertedCoins,
            $this->changeCoins,
            $this->stock,
        ));
    }
}
