<?php

namespace Symfony\Command;

use Application\Customer\GetBalance\GetBalance;
use Application\Maintenance\EnableService\EnableService;
use Domain\Exception\DomainException;
use Domain\Model\Customer;
use Domain\Repository\VendingMachineRepositoryInterface;
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
        private readonly EnableService $enableService,
        private readonly GetBalance $getBalance,
        private readonly VendingMachineRepositoryInterface $machines,
        private readonly VendingMachineOperationFactory $operationFactory,
        private readonly MaintenanceOperationFactory $maintenanceOperationFactory,
    ) {
        parent::__construct();
        $this->customer = new Customer('customer-'.uniqid());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = $this->getHelper('question');
        $output->writeln('<info>Vending machine started.</info>');

        while ($this->mainMenu($questionHelper, $input, $output)) {
        }

        return Command::SUCCESS;
    }

    private function mainMenu(QuestionHelper $helper, InputInterface $input, OutputInterface $output): bool
    {
        $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
            'Choose an operation:',
            ['1' => 'Customer', '2' => 'Maintenance', 'q' => 'Exit'],
            '1',
        ));

        if ('q' === $choice) {
            return false;
        }

        if ('1' === $choice) {
            $this->customerMenu($helper, $input, $output);
        } elseif ('2' === $choice) {
            $this->maintenanceMenu($helper, $input, $output);
        }

        return true;
    }

    /**
     * @param list<string> $labels
     *
     * @return array<int, string>
     */
    private function choices(array $labels): array
    {
        $choices = [];
        foreach ($labels as $index => $choice) {
            $choices[$index + 1] = $choice;
        }

        return $choices;
    }

    private function getBalance(): float
    {
        return ($this->getBalance)() / 100;
    }

    private function customerMenu(QuestionHelper $helper, InputInterface $input, OutputInterface $output): void
    {
        while (true) {
            $choices = $this->choices($this->operationFactory->choices());
            $balance = $this->getBalance();
            $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
                'Customer operation: '.PHP_EOL.sprintf('(Current balance: %.2f EUR)', $balance),
                $choices,
                '1',
            ));

            try {
                $operation = $this->operationFactory->create($choice);
                $commandInput = null;
                if ($operation->hasPrompt()) {
                    $commandInput = $this->ask($helper, $input, $output, new Question($operation->getPrompt()));
                }
                $returnToMainMenu = $operation->execute($commandInput, $this->customer);

                if ($operation->hasOutput()) {
                    $output->writeln($operation->getOutput());
                }

                if ($returnToMainMenu) {
                    return;
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
                ($this->enableService)((string) $this->ask($helper, $input, $output, new Question('Maintenance code: '.PHP_EOL)));
            } catch (DomainException|\InvalidArgumentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

                return;
            }

            $output->writeln('<info>Maintenance mode activated.</info>');
        }

        while (true) {
            $choice = $this->ask($helper, $input, $output, new ChoiceQuestion(
                'Maintenance operation:',
                $this->choices($this->maintenanceOperationFactory->choices()),
                '1',
            ));

            try {
                $operation = $this->maintenanceOperationFactory->create($choice);
                $commandInput = null;
                if ($operation->hasPrompt()) {
                    $commandInput = $this->ask($helper, $input, $output, new Question($operation->getPrompt()));
                }
                $returnToMainMenu = $operation->execute($commandInput);

                if ($operation->hasOutput()) {
                    $output->writeln($operation->getOutput());
                }

                if ($returnToMainMenu) {
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
}
