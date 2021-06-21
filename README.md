# API based user forum with Laravel
# Prerequisite
- Apache
- PHP
- MySQL
- Composer 
# Installation Steps
1. Clone repository
2. Open terminal and run the command "composer install"
3. Create a new Database on MySQL
4. Copy the file ".env.example" and rename it as ".env"
5. Open file ".env" and change the Database configurations accordingly
6. Open terminal and execute "php artisan migrate"
# HTTP status and error codes
- 200 - Success
- 201 - Success
- 401 - Authentication failed
- 403 - Permission denied to perform the requested action
- 404 - Either there is no API method associated with the URL path of the request, or the request refers to one or more resources that were not found
- 405 - Request method is not allowed
- 422 - User input validation error
- 500 - Internal Error
