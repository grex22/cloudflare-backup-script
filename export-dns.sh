#!/usr/bin/bash

php /path/to/exporter/directory/dns-export.php
cd /path/to/exporter/directory/exports/
git add -A
git commit -m "update"
git push -u origin main