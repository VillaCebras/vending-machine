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

#[AsCommand(name: 'vending-machine:run', description: 'Executa la màquina expenedora en mode interactiu.')]
final class VendingMachineCommand extends Command
{
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = $this->getHelper('question');
        $output->writeln('<info>Màquina expenedora iniciada.</info>');

        while (true) {
            $choice = $this->ask($questionHelper, $input, $output, new ChoiceQuestion(
                'Tria una operació',
                ['1' => 'Client', '2' => 'Manteniment', 'q' => 'Sortir'],
                '1',
            ));

            try {
                if ('q' === $choice) {
                    return Command::SUCCESS;
                }

                if ('1' === $choice) {
                    $this->customerMenu($questionHelper, $input, $output);
                } elseif ('2' === $choice) {
                    $this->maintenanceMenu($questionHelper, $input, $output);
                }
            } catch (DomainException|\InvalidArgumentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            }
        }
    }

    private function customerMenu(QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        $customer = new Customer((string) $this->ask($helper, $input, $output, new Question('Identificador del client', 'customer')));
        $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
            'Operació de client',
            ['1' => 'Introduir moneda', '2' => 'Comprar producte', '3' => 'Retornar monedes', 'q' => 'Enrere'],
            '1',
        ));

        if ('1' === $choice) {
            $amount = $this->ask($helper, $input, $output, new Question('Import de la moneda (0.05, 0.10, 0.25 o 1.00)'));
            $total = ($this->insertMoney)($customer, Coin::fromAmount((string) $amount));
            $output->writeln(sprintf('Total introduït: %.2f EUR', $total));
        } elseif ('2' === $choice) {
            $product = Product::fromName((string) $this->ask($helper, $input, $output, new Question('Producte (WATER, SODA o JUICE)')));
            $change = ($this->getItem)($customer, $product);
            $output->writeln(sprintf('Producte servit. Canvi: %s', $this->formatCoins($change)));
        } elseif ('3' === $choice) {
            $coins = ($this->returnCoins)($customer);
            $output->writeln(sprintf('Monedes retornades: %s', $this->formatCoins($coins)));
        }
    }

    private function maintenanceMenu(QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        ($this->enableService)((string) $this->ask($helper, $input, $output, new Question('Codi de manteniment')));
        $output->writeln('<info>Mode de manteniment activat.</info>');

        while ($this->machines->get()->isInMaintenance()) {
            $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
                'Operació de manteniment',
                ['1' => 'Afegir productes', '2' => 'Afegir monedes de canvi', '3' => 'Desactivar manteniment'],
                '1',
            ));

            if ('1' === $choice) {
                $orders = [];
                foreach (explode(',', (string) $this->ask($helper, $input, $output, new Question('Productes (WATER-3,JUICE-5)'))) as $order) {
                    [$name, $quantity] = array_pad(explode('-', trim($order), 2), 2, null);
                    $orders[] = new RestockOrder(Product::fromName((string) $name), (int) $quantity);
                }
                $this->addItems->execute($orders);
                $output->writeln('<info>Productes afegits.</info>');
            } elseif ('2' === $choice) {
                $coins = array_map(fn (string $amount): Coin => Coin::fromAmount(trim($amount)), explode(',', (string) $this->ask($helper, $input, $output, new Question('Monedes (0.05,0.25,1.00)'))));
                $this->addChange->execute($coins);
                $output->writeln('<info>Monedes afegides.</info>');
            } else {
                ($this->disableService)();
                $output->writeln('<info>Mode de manteniment desactivat.</info>');
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
        return [] === $coins ? 'cap' : implode(', ', array_map(static fn (Coin $coin): string => $coin->amount().' EUR', $coins));
    }
}
