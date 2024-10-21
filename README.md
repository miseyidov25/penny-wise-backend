# PennyWise

PennyWise is a budget management web platform designed to help users efficiently manage their finances through a wallet system. The application allows users to create wallets, track transactions, and categorize expenses, providing a comprehensive overview of their financial health.

## Features

- **User Authentication**: Secure user login and registration.
- **Wallet Management**: Create, read, update, and delete wallets.
- **Transaction Tracking**: Log transactions linked to specific wallets, including categories and currencies.
- **Category Management**: Organize transactions into user-defined categories.
- **Balance Overview**: View wallet balances and total balance across all wallets.

## Technologies Used

- Laravel 11
- PHP 8.3+
- Composer
- Laravel Breeze (API template)

### Prerequisites

- PHP 8.3+
- Composer
- MySQL or another supported database

## Installation

To set up the project locally, follow these steps:

### Step-by-Step Installation

1. **Clone the Repository**

   Open your terminal and run the following command:

   ```bash
   git clone https://github.com/leonov0/penny-wise-backend
   cd pennywise

2. **Install PHP Dependencies**

    composer install

3. **Set Up Environment Variables**

    cp .env.example .env

4. **Generate Application Key, Run Migrations**

    php artisan key:generate

    php artisan migrate

5. **Start the Development Server**

    php artisan serve

### Your backend should now be running at http://localhost:8000. 