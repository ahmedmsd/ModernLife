# ModernLife - Production Management System

A comprehensive Laravel-based production management system built with Filament for managing production requests, projects, tasks, and workflows.

## Features

- **Production Request Workflow**: Multi-phase workflow management (Showroom Review → Factory Intake → Department Assignment → Manufacturing → Quality → Installation)
- **Project Management**: Track projects from request to completion
- **Task Management**: Assign and track tasks across departments
- **Role-Based Access Control**: Comprehensive permissions system using Spatie Laravel Permission
- **File Management**: Secure file upload and access control
- **Zoho CRM Integration**: OAuth-based integration with Zoho CRM
- **Notifications**: Real-time notifications for workflow events

## Requirements

- PHP >= 8.3
- Composer
- Node.js >= 18.x and npm
- MySQL/MariaDB
- Redis (recommended for caching and queues)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ModernLife
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure `.env` file**
   - Set database credentials
   - Configure Redis for caching (optional but recommended)
   - Set up Zoho CRM credentials (if using Zoho integration):
     ```
     ZOHO_CLIENT_ID=your_client_id
     ZOHO_CLIENT_SECRET=your_client_secret
     ZOHO_REFRESH_TOKEN=your_refresh_token
     ZOHO_ACCOUNTS_BASE=https://accounts.zoho.com
     ZOHO_API_BASE=https://www.zohoapis.com
     ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database** (optional)
   ```bash
   php artisan db:seed
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   ```

## Development

### Running the development server

Use the convenient dev script that runs server, queue worker, and Vite:

```bash
composer dev
```

Or run individually:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:listen --tries=1

# Terminal 3: Vite dev server
npm run dev
```

### Code Quality

The project includes PHPStan and Laravel Pint for code quality:

```bash
# Run Pint (code formatting)
./vendor/bin/pint

# Run PHPStan (static analysis)
./vendor/bin/phpstan analyse
```

### Testing

```bash
# Run all tests
composer test

# Or directly
php artisan test
```

## Security Considerations

### File Access

- All file access routes require authentication
- Files are protected by policy checks to ensure users can only access files for production requests they have permission to view
- Use signed URLs for temporary file access if needed

### Debug Routes

- Debug and development routes (`/dev/*`, `/test-notif`) are only available in non-production environments
- Admin-only routes (`/admin/perm-cache-reset`) require admin or super-admin roles

### Zoho Integration

- Zoho OAuth credentials should be kept secure in `.env`
- Never commit `.env` file to version control
- Rotate refresh tokens periodically
- The OAuth callback route is public but should be protected by Zoho's redirect URI validation

## Workflow Overview

### Production Request Flow

1. **Request Creation**: Sales or Showroom Manager creates a production request
2. **Showroom Review** (Indirect requests only): Showroom Manager reviews and approves
3. **Factory Intake**: Factory Manager reviews the request
4. **Department Assignment**: Factory Manager assigns to departments
5. **Purchasing**: Purchasing Manager handles material procurement
6. **Manufacturing**: Department Managers oversee production
7. **Quality Checks**: Quality Manager verifies at multiple stages
8. **Installation**: Installation Manager handles on-site work
9. **Completion**: Request is closed when project is complete

### Request Types

- **Direct**: Goes straight from Sales to Factory Intake
- **Indirect**: Goes through Showroom Review first

## Key Models

- `ProductionRequest`: Main workflow entity
- `Project`: Created from approved production requests
- `ProductionTask`: Tasks within projects
- `Client`: Customer information
- `Department`: Organizational departments
- `Employee`: Employee records linked to users

## Permissions

The system uses Spatie Laravel Permission. Key roles:

- `super-admin`: Full system access
- `admin`: Administrative access
- `factory_manager`: Factory operations
- `showroom_manager`: Showroom operations
- `department_manager`: Department-specific access
- `quality_manager`: Quality control access
- `purchasing_manager`: Purchasing access
- `installation_manager`: Installation access
- `sales`: Sales team access

## Caching

- System settings are cached using Laravel's cache system
- Cache is automatically cleared when settings are updated
- Use `setting_clear('key')` or `setting_clear_all()` to manually clear cache

## Queue Configuration

The application uses queues for background jobs. Configure your queue driver in `.env`:

```env
QUEUE_CONNECTION=redis  # or database, sync, etc.
```

Make sure to run a queue worker:

```bash
php artisan queue:work
```

## Troubleshooting

### Permission Cache Issues

If permissions aren't updating, clear the cache:

```bash
# Via route (requires admin access)
GET /admin/perm-cache-reset

# Or via artisan
php artisan permission:cache-reset
```

### Settings Not Updating

Settings are cached. Clear the cache:

```php
setting_clear('setting_key');
// or
setting_clear_all();
```

### File Access Issues

- Ensure files are stored in `storage/app/public`
- Run `php artisan storage:link` to create the symlink
- Check file permissions on the storage directory

## Contributing

1. Create a feature branch
2. Make your changes
3. Run tests: `composer test`
4. Run code quality tools: `./vendor/bin/pint && ./vendor/bin/phpstan analyse`
5. Submit a pull request

## License

This project is proprietary software.

## Support

For issues and questions, please contact the development team.
