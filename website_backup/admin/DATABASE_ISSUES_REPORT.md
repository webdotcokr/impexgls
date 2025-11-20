# Database Connection Issues Report

## Summary
The admin pages for certificates, clients, and locations are experiencing database errors because the database schema has been modified, but the admin pages are still trying to access columns that no longer exist.

## Findings

### 1. Network Locations Table Issues
The `network_locations` table was restructured (see `database/restructure_network_locations.sql`), removing the following columns that the admin page still references:
- `country_code`
- `country_name`
- `city`
- `latitude`
- `longitude`
- `services`
- `operating_hours`

### 2. Clients Table Issues
The `clients` table was modified through a migration (see `migrate_clients.sql`), removing these columns:
- `website`
- `description`
- `category`
- `category_name`

The migration introduced a new `client_categories` table and added a `category_id` foreign key to the `clients` table.

### 3. Working Pages
Pages like `/admin/news/index.php` and `/admin/links/index.php` work correctly because their table structures haven't been changed.

## Solutions

### Option 1: Fix the Database (Recommended for Quick Fix)
Run the provided SQL script `fix_admin_pages.sql` to add back the missing columns:

```bash
# In phpMyAdmin or MySQL command line:
mysql -u root -p corporate_db < /Applications/MAMP/htdocs/impex/corporate-website/admin/fix_admin_pages.sql
```

This will:
- Add the missing columns back to both tables
- Populate some default values for existing records
- Make the admin pages functional again

### Option 2: Update the Admin Pages (Better Long-term Solution)
Modify the admin pages to work with the new database structure:

1. For `/admin/locations/index.php`:
   - Remove references to country_code, country_name, city
   - Remove latitude/longitude mapping functionality
   - Simplify to just show office_name, address, phone, email

2. For `/admin/clients/index.php`:
   - Join with `client_categories` table to get category names
   - Remove references to website and description fields
   - Update forms to use category_id instead of category

### Option 3: Revert Database Changes
If the new structure wasn't intentional, you could revert to the original schema defined in `database_schema.sql`.

## Recommendation
For immediate functionality, use Option 1 (run the fix script). Then plan to either:
- Keep the extended schema if these fields are needed
- Or properly update the admin pages to match the simplified schema (Option 2)

## Database Connection Settings
The database connection is properly configured in `/config/db-config.php`:
- Host: localhost:8889 (MAMP MySQL port)
- Database: corporate_db
- User: root
- Password: root

The connection itself is working fine - the issues are purely schema-related.