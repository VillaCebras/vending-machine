<?php

namespace Domain\Model;

use Domain\Exception\CustomerModeRequired;
use Domain\Exception\InsufficientFunds;
use Domain\Exception\InvalidMaintenanceCode;
use Domain\Exception\MachineBusy;
use Domain\Exception\MaintenanceModeRequired;
use Domain\Exception\OutOfStock;
use Domain\Service\ChangeCalculator;
use Domain\ValueObject\Coin;
use Domain\ValueObject\Product;

final class VendingMachine
{
    private bool $maintenance = false;
    private ?Customer $customer = null;
    /** @var Coin[] */
    private array $insertedCoins = [];
    /** @var Coin[] */
    private array $changeCoins = [];
    /** @var array<string, int> */
    private array $stock = [];

    protected function enterCustomer(Customer $customer): void
    {
        if (
            $this->maintenance
            || (null !== $this->customer && $this->customer->id !== $customer->id)
        ) {
            throw new MachineBusy();
        }
        $this->customer = $customer;
    }

    public function insertCoin(Coin $coin, Customer $customer): void
    {
        $this->enterCustomer($customer);
        $this->insertedCoins[] = $coin;
    }

    /** @return array{product: Product, change: Coin[]} */
    public function buy(Product $product, ChangeCalculator $calculator): array
    {
        $this->requireCustomer();
        if ($this->insertedAmount() < $product->priceInCents()) {
            throw new InsufficientFunds();
        }
        if (($this->stock[$product->value] ?? 0) < 1) {
            throw new OutOfStock();
        }

        $changeAmount = $this->insertedAmount() - $product->priceInCents();
        $change = $calculator->calculate($changeAmount, $this->changeCoins);
        $this->changeCoins = $this->removeCoins($this->changeCoins, $change);
        $this->changeCoins = [...$this->changeCoins, ...$this->insertedCoins];
        --$this->stock[$product->value];
        $this->insertedCoins = [];
        $this->customer = null;

        return ['product' => $product, 'change' => $change];
    }

    /** @return Coin[] */
    public function returnCoins(Customer $customer): array
    {
        $this->enterCustomer($customer);
        $coins = $this->insertedCoins;
        $this->insertedCoins = [];
        $this->customer = null;

        return $coins;
    }

    public function enableMaintenance(string $code, string $expectedCode): void
    {
        if ($this->maintenance || null !== $this->customer) {
            throw new MachineBusy();
        }
        if (!hash_equals($expectedCode, $code)) {
            throw new InvalidMaintenanceCode();
        }
        $this->maintenance = true;
    }

    public function disableMaintenance(): void
    {
        if (!$this->maintenance) {
            throw new MaintenanceModeRequired();
        }
        $this->maintenance = false;
    }

    /** @param Coin[] $coins */
    public function addChange(array $coins): void
    {
        $this->requireMaintenance();
        $this->changeCoins = [...$this->changeCoins, ...$coins];
    }

    public function addItems(Product $product, int $quantity): void
    {
        $this->requireMaintenance();
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }
        $this->stock[$product->value] = ($this->stock[$product->value] ?? 0) + $quantity;
    }

    public function insertedAmount(): int
    {
        return array_sum(array_map(static fn (Coin $coin): int => $coin->cents, $this->insertedCoins));
    }

    public function isInMaintenance(): bool
    {
        return $this->maintenance;
    }

    public function isCustomerActive(): bool
    {
        return null !== $this->customer;
    }

    public function isCustomer(Customer $customer): bool
    {
        return $this->customer?->id === $customer->id;
    }

    public function customer(): ?Customer
    {
        return $this->customer;
    }

    public function stockOf(Product $product): int
    {
        return $this->stock[$product->value] ?? 0;
    }

    public function availableChange(): int
    {
        return count($this->changeCoins);
    }

    private function requireCustomer(): void
    {
        if (null === $this->customer || $this->maintenance) {
            throw new CustomerModeRequired();
        }
    }

    private function requireMaintenance(): void
    {
        if (!$this->maintenance) {
            throw new MaintenanceModeRequired();
        }
    }

    /** @param Coin[] $source @param Coin[] $removed @return Coin[] */
    private function removeCoins(array $source, array $removed): array
    {
        foreach ($removed as $coinToRemove) {
            foreach ($source as $index => $coin) {
                if ($coin->cents === $coinToRemove->cents) {
                    unset($source[$index]);
                    break;
                }
            }
        }

        return array_values($source);
    }
}
