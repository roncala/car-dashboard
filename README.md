# Car Dashboard (PHP + MySQL)

Dashboard-style car retail site:
- Auth (register/login/logout)
- Profile update (includes address/zip for future dealer recommendations)
- Favorites
- Compare up to 4 cars
- Dashboard stats + charts via API

## Requirements
- PHP 7.4+ (works best on PHP 8.x)
- MySQL/MariaDB
- Apache (shared hosting OK)
- Your `cars` table already has data

## Setup
1) Create `.env` from `.env.example` and fill DB values.
2) Run SQL:
   - `sql/00_schema.sql` (creates users/favorites/dealers, keeps cars table if already exists)
   - `sql/03_triggers.sql` (updated_at trigger for users)
   - `sql/01_seed_dealers.sql` (optional)
3) Ensure `cars` has UNIQUE(company_name, car_name):
   ```sql
   ALTER TABLE cars ADD UNIQUE KEY uq_company_car (company_name, car_name);

