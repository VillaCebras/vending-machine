<?php

namespace Tests\Feature;

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
use Symfony\Command\VendingMachineCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

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
            new InsertMoney($this->repository),
            new GetItem($this->repository, new ChangeCalculator()),
            new ReturnCoins($this->repository),
            new EnableService($this->repository, '1234'),
            new DisableService($this->repository),
            new AddItems($this->repository),
            new AddChange($this->repository),
            $this->repository,
        );
        $command->setHelperSet(new HelperSet([new QuestionHelper()]));

        $tester = new CommandTester($command);
        $tester->setInputs($inputs);
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();

        return $tester;
    }
}
