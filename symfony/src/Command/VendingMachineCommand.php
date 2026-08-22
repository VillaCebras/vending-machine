<?php

namespace Symfony\Command;

use Application\Customer\GetItem\GetItem;
use Application\Customer\InsertMoney\InsertMoney;
use Application\Customer\ReturnCoins\ReturnCoins;
use Application\Maintenance\AddChange\AddChange;
use Application\Maintenance\AddItems\AddItems;
use Application\Maintenance\AddItems\RestockOrder;
use Application\Maintenance\DisableService\DisableService;
use Application\Maintenance\EnableService\EnableService;
use Domain\Exception\DomainException;
use Domain\Model\Customer;
use Domain\Model\Product;
use Domain\Repository\VendingMachineRepositoryInterface;
use Domain\ValueObject\Coin;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'vending-machine:run', description: 'Run the vending machine in interactive mode.')]
final class VendingMachineCommand extends Command
{
    private Customer $customer;

    public function __construct(
        private readonly InsertMoney $insertMoney,
        private readonly GetItem $getItem,
        private readonly ReturnCoins $returnCoins,
        private readonly EnableService $enableService,
        private readonly DisableService $disableService,
        private readonly AddItems $addItems,
        private readonly AddChange $addChange,
        private readonly VendingMachineRepositoryInterface $machines,
    ) {
        parent::__construct();
        $this->customer = new Customer('customer-' . uniqid());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = $this->getHelper('question');
        $output->writeln('<info>Vending machine started.</info>');

        while (true) {
            $choice = $this->ask($questionHelper, $input, $output, new ChoiceQuestion(
                'Choose an operation' . PHP_EOL,
                ['1' => 'Customer', '2' => 'Maintenance', 'q' => 'Exit'],
                '1',
            ));

            if ('q' === $choice) {
                return Command::SUCCESS;
            }

            if ('1' === $choice) {
                $this->customerMenu($questionHelper, $input, $output);
            } elseif ('2' === $choice) {
                $this->maintenanceMenu($questionHelper, $input, $output);
            }
        }
    }

    private function customerMenu(QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        while (true) {
            $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
                'Customer operation' . PHP_EOL,
                ['1' => 'Insert coin', '2' => 'Buy product', '3' => 'Return coins', 'q' => 'Exit'],
                '1',
            ));

            if ('q' === $choice) {
                return;
            }

            try {
                if ('1' === $choice) {
                    $amount = $this->ask($helper, $input, $output, new Question('Coin amount (0.05, 0.10, 0.25 or 1.00): '));
                    $total = ($this->insertMoney)($this->customer, Coin::fromAmount((string) $amount));
                    $output->writeln(sprintf('Total inserted: %.2f EUR', $total));
                } elseif ('2' === $choice) {
                    $product = Product::fromName((string) $this->ask($helper, $input, $output, new Question('Product (WATER, SODA or JUICE): ')));
                    $change = ($this->getItem)($this->customer, $product);
                    $output->writeln(sprintf('Product served. Change: %s', $this->formatCoins($change)));
                } elseif ('3' === $choice) {
                    $coins = ($this->returnCoins)($this->customer);
                    $output->writeln(sprintf('Coins returned: %s', $this->formatCoins($coins)));
                }
            } catch (DomainException|\InvalidArgumentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            }
        }
    }

    private function maintenanceMenu(QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        if (!$this->machines->get()->isInMaintenance()) {
            try {
                ($this->enableService)((string) $this->ask($helper, $input, $output, new Question('Maintenance code: ' . PHP_EOL)));
            } catch (DomainException|\InvalidArgumentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

                return;
            }

            $output->writeln('<info>Maintenance mode activated.</info>');
        }

        while (true) {
            $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
                'Maintenance operation' . PHP_EOL,
                ['1' => 'Add products', '2' => 'Add change coins', '3' => 'Disable maintenance' ],
                '1',
            ));

            try {
                if ('1' === $choice) {
                    $orders = [];
                    foreach (explode(',', (string) $this->ask($helper, $input, $output, new Question('Products (WATER-3, JUICE-5): '))) as $order) {
                        [$name, $quantity] = array_pad(explode('-', trim($order), 2), 2, null);
                        $orders[] = new RestockOrder(Product::fromName((string) $name), (int) $quantity);
                    }
                    $this->addItems->execute($orders);
                    $output->writeln('<info>Products added.</info>');
                } elseif ('2' === $choice) {
                    $coins = array_map(fn (string $amount): Coin => Coin::fromAmount(trim($amount)), explode(',', (string) $this->ask($helper, $input, $output, new Question('Coins (0.05,0.25,1.00)'))));
                    $this->addChange->execute($coins);
                    $output->writeln('<info>Change coins added.</info>');
                } else {
                    ($this->disableService)();
                    $output->writeln('<info>Maintenance mode disabled.</info>');
                    return;
                }
            } catch (DomainException|\InvalidArgumentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            }
        }
    }

    private function ask(QuestionHelper $helper, InputInterface $input, OutputInterface $output, Question $question): mixed
    {
        return $helper->ask($input, $output, $question);
    }

    /** @param Coin[] $coins */
    private function formatCoins(array $coins): string
    {
        return [] === $coins ? 'none' : implode(', ', array_map(static fn (Coin $coin): string => $coin->amount().' EUR', $coins));
    }
}
