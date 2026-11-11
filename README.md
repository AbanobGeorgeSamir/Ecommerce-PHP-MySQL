# PHP and MySQL E-Commerce

Classic e-commerce website built with plain PHP and MySQL, without a framework. It covers the full shopping flow for customers and a separate admin area for managing products, orders and users.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)

<!-- Add screenshots here, for example: ![Home](docs/home.png) -->

## Features

**Customers**
- Product listing and product detail pages
- Registration and login with hashed passwords (`password_hash`, `password_verify`)
- Shopping cart stored in the database: add and remove items
- Checkout and order history
- About and contact pages, with contact messages saved to the database

**Administrators** (`/admin`)
- Separate admin login and dashboard
- Add, edit and delete products
- View and manage orders
- Add, edit and delete users

**Engineering**
- Prepared statements (`mysqli`) for database queries
- Role-based access with an `admin` / `user` role column
- Reusable header, footer and database connection includes
- Relational schema with foreign keys: `users`, `products`, `cart`, `orders`, `order_items`, `messages`

## Tech Stack

PHP, MySQL (InnoDB, `utf8mb4`), HTML5, CSS3 and JavaScript.

## Project Structure

```text
actions/        Form handlers: login, register, logout, cart
admin/          Admin area: dashboard, products, orders, users
includes/       Shared layout files and database connection
uploads/        Product images
ecommerce.sql   Database schema and sample products
*.php           Public pages: index, products, product, cart, checkout, orders, about, contact
```

## Getting Started

**Requirements:** a local PHP and MySQL stack such as XAMPP, WAMP or MAMP.

1. Clone the repository into your web root (for example `htdocs/ecommerce`):

   ```bash
   git clone https://github.com/AbanobGeorgeSamir/Ecommerce-PHP-MySQL.git
   ```

2. Create a database named `ecommerce` and import `ecommerce.sql` (phpMyAdmin, or `mysql -u root -p ecommerce < ecommerce.sql`).

3. Check the connection settings in `includes/db.php`. They default to host `localhost`, user `root`, an empty password and database `ecommerce`. These are local-development defaults, so use your own credentials for anything public.

4. Open `http://localhost/ecommerce` in your browser.

### Creating an admin account

Register a normal account on the site, then promote it in MySQL:

```sql
UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
```

Then sign in at `/admin/login.php`.

## Author

**Abanoub George Samir** · Junior Full-Stack Developer · [Portfolio](https://devabanobgeorge.vercel.app) · [LinkedIn](https://www.linkedin.com/in/abanob-george-samir) · [GitHub](https://github.com/AbanobGeorgeSamir)
