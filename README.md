# POS System - CodeIgniter 4

A complete Point of Sale (POS) system built with CodeIgniter 4 framework featuring two main roles: Admin and Cashier.

## Features

### Admin Features
- **Dashboard**: View system overview with total items, active items, and total users
- **Item Management**: Add, edit, delete, and manage products
  - Item code, name, description, price, and quantity
  - Active/Inactive status
- **User Management**: Create and manage system users
  - Assign roles (Admin or Cashier)
  - Manage user status

### Cashier Features
- **Point of Sale**: Process sales transactions
  - Browse available items
  - Add items to shopping cart
  - Adjust quantities
  - Apply discounts
  - Select payment method (Cash, Card, Check)
- **Sale History**: View past transactions and receipts
- **Receipt**: Print/view detailed sale receipts

## Installation & Setup

### Prerequisites
- XAMPP (or any server with PHP 8.0+ and MySQL)
- Composer (already installed)

### Quick Start

1. **Access the application**: http://localhost/pos/

2. **Default Admin Credentials**:
   - Username: `admin`
   - Password: `admin123`

3. **Default Cashier Credentials**:
   - Username: `cashier`
   - Password: `cashier123`

## Database

The database `pos` has been created and all migrations have been applied. The following tables are available:
- **users**: Store user accounts with roles (admin/cashier)
- **items**: Product inventory
- **sales**: Sales transactions
- **sale_items**: Line items for each sale

## File Structure

```
pos/
├── app/
│   ├── Controllers/
│   │   ├── Auth.php
│   │   ├── Dashboard.php
│   │   ├── Admin/
│   │   │   ├── Dashboard.php
│   │   │   ├── Items.php
│   │   │   └── Users.php
│   │   └── Cashier/
│   │       └── Sales.php
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── ItemModel.php
│   │   ├── SaleModel.php
│   │   └── SaleItemModel.php
│   ├── Views/
│   │   ├── layout.php
│   │   ├── auth/
│   │   ├── admin/
│   │   └── cashier/
│   ├── Database/
│   │   ├── Migrations/ (4 migration files)
│   │   └── Seeds/
│   │       └── UserSeeder.php
│   └── Config/
│       ├── Routes.php
│       └── Database.php
├── .env
└── README.md
```

## User Roles

### Admin
- View dashboard with statistics
- Manage items (Create, Read, Update, Delete)
- Manage users (Create, Read, Update, Delete)

### Cashier
- Process point of sale transactions
- View sale history
- Print receipts

## Usage

### Admin Functions
1. Login with admin credentials
2. Navigate to "Manage Items" to add/edit/delete products
3. Navigate to "Manage Users" to create new cashiers or admins
4. View dashboard for statistics

### Cashier Functions
1. Login with cashier credentials
2. Click on items to add to shopping cart
3. Adjust quantities in the cart
4. Apply discount if needed
5. Select payment method (Cash, Card, Check)
6. Complete sale and view receipt

## Technology Stack
- **Framework**: CodeIgniter 4 (v4.6.4)
- **Language**: PHP 8.2+
- **Database**: MySQL
- **Frontend**: Bootstrap 5
- **Architecture**: MVC

## Key Features
- ✅ Role-based access control
- ✅ Secure password hashing
- ✅ Session-based authentication
- ✅ Inventory management
- ✅ Sales tracking
- ✅ Discount functionality
- ✅ Multiple payment methods
- ✅ Receipt generation

---
**Status**: ✅ Complete and Ready to Use

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
