----------
name: Insert money
description: A customer insert a coin in the vending machine to buy an item.
----------

# Preconditions

- The vending machine is not under maintenance service.
- No other customer is using the vendor machine.

# Description

1. The customer chooses the insert money action
    1.1 The system must notify the user if it has run out if change.
2. Chooses between one of the allowed coins: 0.05, 0.10, 0.25 and 1.
3. The system updates the current money inserted.
4. The system shows the total inserted money by the customer. 
