# 🧾 Laravel POS SaaS (Multi-Tenant)

> Multi-tenant Point of Sale (POS) system built with Laravel + Stancl Tenancy (separate database per tenant)

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Tenancy](https://img.shields.io/badge/Tenancy-stancl-green)
![License](https://img.shields.io/badge/license-MIT-brightgreen)

---

## 🚀 Features

* ✅ Multi-tenant architecture (separate DB per tenant)
* ✅ Auto database creation per tenant
* ✅ Subdomain-based tenant system
* ✅ POS system (products, orders, customers)
* ✅ Central admin panel
* ✅ Secure tenant isolation
* ✅ Laravel Blade UI

##new features
✅ Multiple domain support
✅ Per-domain database
✅ Per-domain FTP
✅ Per-domain uploads
✅ Shared Laravel codebase
✅ Dynamic tenant switching

---

## 🏗️ Architecture

### 🔹 Central App (Main Domain)

* Manages tenants
* Handles signup/login
* Stores:

  * tenants
  * domains

### 🔹 Tenant App (Subdomain)

Each tenant has:

* Separate database
* POS data:

  * products
  * orders
  * customers

---

## 🌐 Domain Structure

```
yourpos.com              → Main App
shop1.yourpos.com        → Tenant 1
shop2.yourpos.com        → Tenant 2
```

---

## ⚙️ Installation

### 1️⃣ Clone Repository

```bash
git clone https://github.com/your-username/pos-saas.git
cd pos-saas
```

---

### 2️⃣ Install Dependencies

```bash
composer install
```

---

### 3️⃣ Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=main_db
DB_USERNAME=root
DB_PASSWORD=
```

---

### 4️⃣ Install Tenancy

```bash
composer require stancl/tenancy
php artisan tenancy:install
php artisan migrate
```

---

### 5️⃣ Configure Database

`config/database.php`:

```php
'default' => 'mysql',

'connections' => [
    'mysql' => [...], // central DB
    'tenant' => [...], // tenant DB
]
```

---

### 6️⃣ Configure Tenancy

`config/tenancy.php`:

```php
'central_domains' => [
    '127.0.0.1',
    'localhost',
],
```

---

## 🧪 Local Development

Edit your hosts file:

```
127.0.0.1 yourpos.com
127.0.0.1 shop1.localhost
127.0.0.1 shop2.localhost
```

---

## 🏪 Create Tenant

### Endpoint

```
POST /create-tenant
```

### Request Body

```json
{
  "name": "shop1",
  "email": "shop1@gmail.com",
  "password": "123456"
}
```

---

## ⚡ Auto Database Setup

Handled using event listener:

```php
Event::listen(TenantCreated::class, function ($event) {
    dispatch(new CreateDatabase($event->tenant));
    dispatch(new MigrateDatabase($event->tenant));
});
```

---

## 🗂️ Tenant Migrations

Directory:

```
database/migrations/tenant
```

Example:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->double('price');
    $table->timestamps();
});
```

---

## ▶️ Run Project

```bash
php artisan serve
php artisan queue:work
php artisan tenants:migrate
php artisan optimize:clear
```

---

## ⚠️ Important Notes

* ❌ Avoid raw DB queries (use Eloquent)
* ❌ Do not store tenant data in central DB
* ✅ Always use subdomain for tenant isolation
* ✅ Run queue worker for DB creation

---

## 🔐 Security

* Separate database per tenant
* No cross-tenant access
* Password hashing enabled

---

## 📦 Roadmap

* 💳 Subscription system (Stripe / bKash)
* 👥 Role management (Admin / Cashier)
* 📊 Reports dashboard
* 🌐 Custom domains per tenant
* 📱 API support

---

## 👨‍💻 Author

**Ashik**
Laravel SaaS Developer

---

## 📄 License

MIT License

---

## ⭐ Support

If you like this project, give it a ⭐ on GitHub!

---
