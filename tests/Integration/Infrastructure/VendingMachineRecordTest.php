<?php

namespace Tests\Integration\Infrastructure;

use Application\Maintenance\AddItems\RestockOrder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Domain\Model\Customer;
use Domain\Model\Product;
use Domain\Model\VendingMachine;
use Domain\ValueObject\Coin;
use Infrastructure\Doctrine\VendingMachineRecord;
use Infrastructure\DoctrineVendingMachineRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VendingMachineRecordTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrineVendingMachineRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel(['environment' => 'test']);

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->entityManager = $entityManager;
        $this->repository = new DoctrineVendingMachineRepository($this->entityManager);
        $this->ensureSchema();
        $this->entityManager->getConnection()->beginTransaction();
        $this->entityManager->getConnection()->executeStatement('DELETE FROM vending_machine');
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollBack();
        }

        $this->entityManager->close();

        parent::tearDown();
    }

    protected static function getKernelClass(): string
    {
        return \Symfony\Kernel::class;
    }

    public function testSavesMachineToDatabase(): void
    {
        $customer = new Customer('customer-1');
        $machine = new VendingMachine();
        $machine->enableMaintenance('code', 'code');
        $machine->addChange([Coin::fromCents(5)]);
        $machine->addItems(new RestockOrder(Product::fromName('SODA'), 2));
        $machine->disableMaintenance();
        $machine->insertCoin(Coin::fromCents(25), $customer);

        $this->repository->save($machine);
        $this->entityManager->clear();

        $row = $this->entityManager->getConnection()->fetchAssociative(
            'SELECT id, maintenance, customer_id, inserted_coins, change_coins, stock FROM vending_machine WHERE id = 1'
        );

        $this->assertNotFalse($row, 'The vending machine was not stored in MySQL.');
        $this->assertSame(1, (int) $row['id']);
        $this->assertSame(0, (int) $row['maintenance']);
        $this->assertSame('customer-1', $row['customer_id']);
        $this->assertSame([25], $this->decodeJson($row['inserted_coins']));
        $this->assertSame([5], $this->decodeJson($row['change_coins']));
        $this->assertSame(['SODA' => 2], $this->decodeJson($row['stock']));

        $restored = $this->repository->get();

        $this->assertTrue($restored->isCustomer($customer));
        $this->assertSame(25, $restored->insertedAmount());
        $this->assertSame(1, $restored->availableChange());
        $this->assertSame(2, $restored->stockOf(Product::fromName('SODA')));
    }

    private function ensureSchema(): void
    {
        $schemaManager = $this->entityManager->getConnection()->createSchemaManager();
        if ($schemaManager->tablesExist(['vending_machine'])) {
            return;
        }

        (new SchemaTool($this->entityManager))->createSchema([
            $this->entityManager->getClassMetadata(VendingMachineRecord::class),
        ]);
    }

    /** @return array<mixed> */
    private function decodeJson(mixed $value): array
    {
        if (\is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);
        $this->assertIsArray($decoded);

        return $decoded;
    }
}
