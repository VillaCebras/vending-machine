# vending-machine

This is a demo project. Represents a vending machine operative customer and maintenance actions.

## Documentation

Docs about the project are stored at `docs` folder (Specs + Architecture).

## Execute steps

1. ``make up``: Builds docker images and execute composer install to update the dependencies.
2. ``make migrate``: Creates database schema and tables.
3. ``make run``: Starts main shell symfony command.

## Debugging

Enable XDEBUG in each operation by adding `DEBUG=1` parameter in any Make execution.

Example: ``make test DEBUG=1``