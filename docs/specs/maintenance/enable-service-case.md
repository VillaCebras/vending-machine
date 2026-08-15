----------
name: Activates the service mode
description: The service team activates the maintenance mode to perform system tasks.
----------

# Preconditions

- The vending machine is not under maintenance service.
- Any customer is using the vendor machine.

# Description

1. The system asks for a service worker maintenance code.
    1.1 Shows an error if the code is not valid and returns.
2. The system enables the maintenance mode. No other customer can use it.