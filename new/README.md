# mls_mapper
Using the Mozilla Location Services data for fun

# How to use correctly

Download a dataset from [Mozilla](https://location.services.mozilla.com/downloads)

Extract so that you have the CSV, it will be named like: MLS-full-cell-export-YYYY-MM-DDT000000.csv

## Using mls\_mcc\_sort\_mem

**This file uses lots of RAM and is much quicker than the mls\_mcc\_sort\_file method**

Download a 64-bit version of PHP, you'll need it.

Tweak the php.ini to have a high memory limit, I went for:
    memory_limit = 8192M

From the command line, run:
    php.exe mls\_mcc\_sort\_mem.php

Type the location of your CSV (MLS Dataset), then the MCC, then the RAT (e.g. LTE,UMTS,GSM)

## Using mls\_mcc\_sort\_file

**This version uses less RAM but takes a lot longer**

From the command line, run:
    php.exe mls\_mcc\_sort\_file.php

Type the location of your CSV (MLS Dataset), then the MCC, then the RAT (e.g. LTE,UMTS,GSM)

## Updating the DB

By default, this is set to work with UK networks, you can program in your own eNB/sector ID patterns

Import the DB Schema if you haven't already, it's in lte_cell_export.sql

Check the database info in the update-db.php file to make sure it all works...

From the command line, run:
    php.exe update-db.php

Type the location of your smaller CSV (the one you got from the mls_mcc_sort program)

**It will take a while** but then you'll have all the MLS data in a database, whoop!