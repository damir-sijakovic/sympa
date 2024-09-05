#!/bin/bash

# Check if PHP is installed
if command -v php >/dev/null 2>&1; then
    echo "PHP is installed."
else
    echo "PHP is not installed."
fi

# Check if MariaDB is installed
if command -v mysql >/dev/null 2>&1; then
    echo "MariaDB is installed."
else
    echo "MariaDB is not installed."
fi
