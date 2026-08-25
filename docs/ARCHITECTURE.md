# ARCHITECTURE

This project runs a basic php symfony command which uses a MySQL database to store the vending machine status.

This project runs in a PHP 8.5 environment with pdo_mysql and xdebug extension required plus main symfony extensions.

Symfony version is locked at 8.1.*.

## File structure

```
.
├── docker-compose.yml
├── Application/
├── Domain/
├── Domain/
├── Infrastructure
├── docs/
│   └── ARCHITECTURE.md
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── xdebug.ini
├── symfony/
│   ├── bin/
│   ├── config/
│   ├── migrations/
│   ├── public/
│   ├── src/
│   │   ├── Command/
│   │   │   ├── BuyProductOperation.php
│   │   │   └── VendingMachineCommand.php
│   │   └── ...
│   ├── tests/
│   ├── var/
│   └── vendor/
└── README.md
```
Main folders/files:

```
- `Application/`: Use case and application services definitions. Divided in Customer and Maintenance contexts.
- `Domain/`: Domain enities and value objects location.
- `docker/`: Docker-specific configurations.
- `docs/`: Basic specs defintion and other project related documentation.
- `Infrastructure/`: Implementations of open Domain / Application interfaces.
- `symfony/`: Symfony PHP application and resources.
- `tests/`: PHPUnit test battery.
```

## Entrypoint

``VendingMachineCommand.php`` under symfony folder runs the project's main process through CLI.

## Database

Vending machine actual state is stored in a MySQL schema managed by Doctrine. Database schema is located: ``symfony/migrations``, and model implementation lives ``Infrastructure/DoctrineVenfingMachineRepository.php``.