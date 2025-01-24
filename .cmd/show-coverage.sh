#!/bin/bash

# FROM ROOT FOLDER:   .cmd/show-coverage.sh


# Show reports (WSL2)
powershell.exe -c reports/testdox.html
powershell.exe -c reports/coverage/index.html
