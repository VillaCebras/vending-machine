----------
name: Insert money
description: A customer insert a coin in the vending machine to buy an item.
----------

# Preconditions

- The vending machine is not under maintenance service.

# Description

1. The systrem locks the vending machine for the customer if it was free.
    1.1. If the vending machine is used by another user it shows an error.
2. The system must notify the user if it has run out if change.
3. Chooses between one of the allowed coins: 0.05, 0.10, 0.25 and 1.
4. The system updates the current money inserted.
5. The system shows the total inserted money by the customer. 
