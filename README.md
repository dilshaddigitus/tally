# Tally Connector SaaS — Laravel Application

Phase 2 of the project: Laravel foundation (auth, roles & permissions, customer management, tenant
isolation, admin dashboard layout). See the Phase 1 architecture document for the full system design.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.x
- Node (only needed later, for frontend asset building — not required for Phase 2)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` for your MySQL instance, then:

```bash
php artisan migrate --seed
```

Seeding creates three demo accounts (password for all: `password`):

| Role | Email |
| --- | --- |
| Super Admin | `admin@tallyconnector.test` |
| Customer Admin | `customer.admin@tallyconnector.test` |
| Customer Staff | `customer.staff@tallyconnector.test` |

## Running

```bash
php artisan serve
```

## Testing

Tests run against an in-memory SQLite database (configured in `phpunit.xml`), not your dev MySQL database:

```bash
php artisan test
```

## What's implemented (Phase 2)

- Email/password authentication, including forgot/reset password
- Three roles: Super Admin, Customer Admin, Customer Staff (`users.role`), plus a `permissions` /
  `permission_user` table for configuring what an individual Customer Staff member can see
- Tenant isolation: `App\Models\Concerns\BelongsToCustomer` applies a global query scope and
  auto-stamps `customer_id` on create for every tenant-owned model; a Super Admin explicitly bypasses it
- Customer management: Super Admin can register a customer (creates the customer, its profile, its
  first subscription and its Customer Admin user in one transaction), list/search, edit, activate/deactivate
- Subscriptions support both a recurring plan and a one-time-fee plan on the same `subscriptions` /
  `subscription_plans` tables (`billing_type`); no payment gateway is integrated yet — Super Admin marks
  payment status manually
- Admin dashboard (customer counts) and a Customer portal dashboard (own business + subscription)
- Automated tests: login, role enforcement, and tenant isolation (`tests/Feature/TenantIsolation`)

## Deliberate deviations from the Phase 1 database design doc

- `permissions`/`permission_user` replaces the originally sketched `role_permissions` table — the brief
  asks for configurable-per-staff-member permissions, which is finer-grained than per-role
- `customer_users` (multi-customer-per-login) is not built yet — out of scope until it's actually needed
