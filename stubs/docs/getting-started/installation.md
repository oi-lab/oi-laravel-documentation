---
title: Installation
description: How to install and configure the application
section: getting-started
order: 2
---

# Installation

This guide will help you install and configure the application.

## Prerequisites

Before you begin, ensure you have the following installed:

- PHP 8.2 or higher
- Composer
- Node.js and npm
- A database (MySQL, PostgreSQL, SQLite)

## Installation Steps

### 1. Clone the repository

```bash
git clone https://github.com/your-repo/your-app.git
cd your-app
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Build assets

```bash
npm run build
```

## Verification

To verify your installation is working:

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.
