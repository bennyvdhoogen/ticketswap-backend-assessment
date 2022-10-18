# Remarks
- Fun assessment, cheers!
- Please checkout `main` for my completed codebase.
- Run `composer install` to install dependencies (as they are excluded in the zip)
- At some point I made an architectural decision to refactor the passing of IDs on Listing/Ticket creation to generating them on create/construct
  - My thinking was: a Ticket and Listing should not be seen as transferable, but more like transactions. Buyers and Sellers essentially exchange valid Barcodes (which stay the same after transaction),
  - This was primarily done to work out being able to resell tickets previously sold in the Marketplace
  - Codebase prior to this decision can eventually be viewed in the `before-ticket-id-refactor` branch -- but purely for historic purposes. My submitted work is in main.
