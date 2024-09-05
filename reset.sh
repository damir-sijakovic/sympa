#!/bin/bash

# Variables
DB_HOST="localhost"
DB_USER="sympa"
DB_PASS="sympa"
DB_NAME="sympa"

# Check if the database exists
DB_EXIST=$(sudo mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" | grep "$DB_NAME")

if [ -z "$DB_EXIST" ]; then
    echo "Database '$DB_NAME' does not exist. Creating it now..."
    sudo mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME;"
    echo "Database '$DB_NAME' created."
else
    # Confirm with the user
    read -p "Are you sure you want to erase all content in the database '$DB_NAME'? This action cannot be undone. Type 'yes' to proceed: " confirm

    if [ "$confirm" == "yes" ]; then
        # Drop and recreate the database
        sudo mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE $DB_NAME; CREATE DATABASE $DB_NAME;" 
        echo "All content in the database '$DB_NAME' has been erased."
    else
        echo "Operation canceled."
        exit 1
    fi
fi

# Remove migrations and perform the migration process
MIGRATIONS_DIR="./migrations"
rm -rf $MIGRATIONS_DIR/*

php bin/console cache:clear --env=prod
php bin/console cache:clear --env=dev
php bin/console make:migration
php bin/console doctrine:migrations:migrate
