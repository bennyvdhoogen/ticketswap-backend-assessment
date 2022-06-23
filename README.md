# TicketSwap assessment
Your assignment is to implement a new marketplace system for selling and buying tickets. Some tests are already written and there is a basic implementation for you to finish. Even though it is not real code, please treat it as if it would be deployed to thousands of users.

There are some concrete classes for implementation and there are some unit tests. Your job is to:
- make sure all the business logic above is implemented and tested
- make sure all the tests pass
- implement the missing functionality in the skipped tests

The business rules are as follows:
- A listing contains multiple tickets. 
- Tickets can contain multiple barcodes.
- Sellers can create listings with tickets.
- Buyers can buy individual tickets from a listing.
- Once all tickets have been sold for a listing, it is no longer for sale.
- It should not be possible to create a listing with duplicate barcodes in it.
- It should not be possible to create a listing with duplicate barcodes within another listing.
- Though, it should be possible for the last buyer of a ticket, to create a listing with that ticket (based on barcode).

You are free, encouraged even, to rewrite those parts in the implementation, tests or overal design that you think are in need of it. You are also free to add any library that you think you need. We do however ask that you don't use a database to make the business rules work.
If you have any questions you can always [contact us][contact].

### Bonus
We know some sellers have nefarious plans and try to scam buyers. To prevent that, we want to only sell listings that have been verified by an admin. Make the implementation, and the accompanying unit tests, to be able to verify listings and only buy tickets from verified listings.

## Setup
First of all, it is assumed that you can run PHP (8.0+) and composer locally.

### Download repository
You can download/clone the repo to your local machine and follow the next instructions to get everything up and running.

### Installing dependencies
```
composer install
```

### Start unit tests
```
vendor/bin/phpunit tests
```

## Handing in the assessment
When you're finished developing the application and did all the git commits, you can zip the entire folder (don't forget the .git), and send it to us in reply of the mail you got with this assessment. If there are issues sending the zip file, please use a service like https://wetransfer.com to upload the zip file there and send us a link to download the files.

## Good luck
And again if you have any questions, [let us know][contact]!

[contact]: mailto:angele@ticketswap.com


