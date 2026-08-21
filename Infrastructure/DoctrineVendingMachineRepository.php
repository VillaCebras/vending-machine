<?php

namespace Infrastructure;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Model\VendingMachine;
use Domain\Repository\VendingMachineRepositoryInterface;
use Infrastructure\Doctrine\VendingMachineRecord;

final class DoctrineVendingMachineRepository implements VendingMachineRepositoryInterface
{
    private const MACHINE_ID = 1;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function get(): VendingMachine
    {
        return $this->findRecord()?->toDomain() ?? new VendingMachine();
    }

    public function save(VendingMachine $machine): void
    {
        $record = $this->findRecord() ?? new VendingMachineRecord(self::MACHINE_ID);
        $record->syncFrom($machine);

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    private function findRecord(): ?VendingMachineRecord
    {
        return $this->entityManager->find(VendingMachineRecord::class, self::MACHINE_ID);
    }
}
