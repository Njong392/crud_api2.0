# Symfony Crud Application
This is a basic CRUD application for Symfony. The API allows; 

- Users to sign up, register, create products, and add those products to categories. 
- Users to have roles, and an admin user can create categories.
- When creating a form, a user also has the possibility of uploading the product image

# Project Set-up
- This project can be set up via docker
- Fork and clone the project
- Run `docker compose build` to set up the project base dependencies
- Run `docker compose up` to start the containers 
- Create a `.env` in the root of the project

```php
DATABASE_URL="..."
DATABASE_NAME="..."
DATABASE_PASSWORD="..."
DATABASE_USER="..."
DATABASE_ROOT_PASSWORD="..."

