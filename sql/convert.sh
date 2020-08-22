#!/bin/sh
iconv -f utf-8 -t latin2 init_data.sql.utf8 > init_data.sql
iconv -f utf-8 -t latin2 test_data.sql.utf8 > test_data.sql
