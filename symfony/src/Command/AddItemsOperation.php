<?php

namespace Symfony\Command;

use Application\Maintenance\AddItems\AddItems;
use Application\Maintenance\AddItems\RestockOrder;
use Domain\Model\Product;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('Add products', 30)]
class AddItemsOperation implements MaintenanceOperation
{
    public function __construct(
        private readonly AddItems $addItems,
    ) {
    }

    public function hasPrompt(): bool
    {
        return true;
    }

    public function getPrompt(): string
    {
        return 'Products (WATER-3, JUICE-5): ';
    }

    public function hasOutput(): bool
    {
        return true;
    }

    public function getOutput(): string
    {
        return '<info>Products added.</info>';
    }

    public function execute(?string $input): bool
    {
        $orders = [];
        foreach (explode(',', (string) $input) as $order) {
            [$name, $quantity] = array_pad(explode('-', trim($order), 2), 2, null);
            $orders[] = new RestockOrder(Product::fromName((string) $name), (int) $quantity);
        }
        $this->addItems->execute($orders);

        return false;
    }
}
