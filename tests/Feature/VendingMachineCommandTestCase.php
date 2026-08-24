<?php

namespace Tests\Feature;

use Application\Customer\GetBalance\GetBalance;
use Application\Customer\GetItem\GetItem;
use Application\Customer\InsertMoney\InsertMoney;
use Application\Customer\ReturnCoins\ReturnCoins;
use Application\Maintenance\AddChange\AddChange;
use Application\Maintenance\AddItems\AddItems;
use Application\Maintenance\DisableService\DisableService;
use Application\Maintenance\EnableService\EnableService;
use Domain\Service\ChangeCalculator;
use Infrastructure\InMemoryVendingMachineRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Command\AddChangeOperation;
use Symfony\Command\AddItemsOperation;
use Symfony\Command\BuyProductOperation;
use Symfony\Command\DisableServiceOperation;
use Symfony\Command\InsertCoinOperation;
use Symfony\Command\MaintenanceOperationFactory;
use Symfony\Command\ReturnCoinsOperation;
use Symfony\Command\VendingMachineCommand;
use Symfony\Command\VendingMachineOperationFactory;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ServiceLocator;

abstract class VendingMachineCommandTestCase extends TestCase
{
    protected InMemoryVendingMachineRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryVendingMachineRepository();
    }

    /** @param list<string> $inputs */
    protected function runCommand(array $inputs): CommandTester
    {
        $command = new VendingMachineCommand(
            new EnableService($this->repository, '1234'),
            new GetBalance($this->repository),
            $this->repository,
            new VendingMachineOperationFactory(new ServiceLocator([
                'Insert coin' => fn (): InsertCoinOperation => new InsertCoinOperation(new InsertMoney($this->repository)),
                'Buy product' => fn (): BuyProductOperation => new BuyProductOperation(new GetItem($this->repository, new ChangeCalculator())),
                'Return coins' => fn (): ReturnCoinsOperation => new ReturnCoinsOperation(new ReturnCoins($this->repository)),
            ])),
            new MaintenanceOperationFactory(new ServiceLocator([
                'Add products' => fn (): AddItemsOperation => new AddItemsOperation(new AddItems($this->repository)),
                'Add change coins' => fn (): AddChangeOperation => new AddChangeOperation(new AddChange($this->repository)),
                'Disable maintenance' => fn (): DisableServiceOperation => new DisableServiceOperation(new DisableService($this->repository)),
            ])),
        );
        $command->setHelperSet(new HelperSet([new QuestionHelper()]));

        $tester = new CommandTester($command);
        $tester->setInputs($inputs);
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();

        return $tester;
    }
}
