# Boutique — Client Management Web App

Full-stack web application built as a 7th-semester academic project 
for a small retail client. Provides user authentication and a 
foundation for managing boutique inventory and customers.

**Stack:** PHP · MySQL · HTML/CSS/JavaScript

## Features

- User authentication (email/password login)
- Session-protected pages with automatic redirect to login
- Customer registration module
- Inventory consultation module
- Responsive UI

## Setup

1. Clone the repo:
   `git clone https://github.com/Danielacod7/Proyecto-Boutique.git`

2. Import the database schema from `/database/schema.sql` into 
   your MySQL server.

3. Configure your database connection in `backend/config.php`:
```php
   $db_host = "localhost";
   $db_name = "boutique";
   $db_user = "your_user";
   $db_pass = "your_password";
```

4. Serve the project via PHP's built-in server or Apache/Nginx.

## Team

Built by [Danielacod7](https://github.com/Danielacod7),
[VictorJaBa](https://github.com/VictorJaBa), and
[Goril0](https://github.com/Goril0).
