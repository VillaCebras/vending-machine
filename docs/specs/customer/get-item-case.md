----------
name: Get item
description: A customer exchanges the money for an item
----------

# Preconditions

- The vending machine is not under maintenance service.
- No other customer is using the vendor machine.

# Description

1. The system shows the total inserted money by the customer. 
2. The system lists all available items and its cost.
3. The customer chooses an item.
    3.1 The system shows an error if the amount does not cover the item's cost.
    3.2 The user can choose to finsih without getting an item.
4. The system shows the item bought and the change returned.
    4.1 the system may not return any change if it has run out of coins.
5. It updates the item's availability and the available change.
6. The vending machine is locked for the customer.