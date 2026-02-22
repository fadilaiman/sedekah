# Sedekah.info

A community-driven QR code aggregator for Islamic charities in Malaysia.

![Status](https://img.shields.io/badge/status-MVP%20Development-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Vue](https://img.shields.io/badge/Vue-3-green)
![License](https://img.shields.io/badge/license-MIT-green)

## About Sedekah.info

**Sedekah.info** is a platform that aggregates QR codes from Islamic charitable institutions across Malaysia, making it easy for donors to support organizations with a single scan. The platform enables:

- **Public Users**: Browse, search, and filter charitable institutions by category, location, or name
- **Community Contributors**: Submit new institutions for approval via email authentication
- **Super Admins**: Manage institutions, approve submissions, upload QR codes, and view analytics

The platform handles QR code display and donation links only—all payments are processed by the institutions themselves.

## Key Features

### For Donors
✅ Browse 500+ charitable institutions
✅ Search by name, category (mosque, school, orphanage, healthcare, food bank, NGO)
✅ Filter by state and city
✅ View institution details, QR codes, and contact information
✅ Share institutions via WhatsApp, Facebook, or direct link
✅ Download QR codes for offline sharing
✅ Mobile-responsive design with PWA support

### For Community
✅ Submit new institutions with email magic-link authentication
✅ Live duplicate detection
✅ Upload QR code images
✅ Track submission status

### For Administrators
✅ Dashboard with institution and submission statistics
✅ Full CRUD operations for institutions
✅ QR code management (upload, activate, deactivate)
✅ Submission approval/rejection workflow
✅ Bulk CSV import with conflict resolution
✅ Featured institutions curation
✅ Audit logging of all admin actions
✅ Analytics and reporting

## Tech Stack

- **Backend**: Laravel 12 + PHP 8.3
- **Frontend**: Vue 3 (Composition API) + Inertia.js + Tailwind CSS v3
- **Database**: SQLite (dev), MySQL 8.0+ (prod)
- **Storage**: Local filesystem (dev), Cloudflare R2 (prod)
- **Authentication**: Email magic-links (Sanctum + Laravel Breeze)
- **Infrastructure**: Docker + Kubernetes
- **CDN**: Cloudflare

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+
- npm or yarn
- SQLite or MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/sedekah-info.git
   cd sedekah-info
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build frontend assets**
   ```bash
   npm run build
   ```

6. **Start development servers**
   ```bash
   # Terminal 1: Laravel server
   php artisan serve

   # Terminal 2: Vite dev server
   npm run dev
   ```

Visit `http://localhost:8000` in your browser.

### Default Admin Credentials (Development)

- **Email**: admin@sedekah.info
- **Password**: changeme

⚠️ Change these immediately in production.

## Development Workflow

### Before Starting Any Task

1. **Read the PRD** — All requirements are in `docs/PRD/`
   - Start with: `docs/PRD/00_DEVELOPMENT_REQUIREMENTS.md`
   - Database: `docs/PRD/05_Backend/database.md`
   - API Spec: `docs/PRD/05_Backend/api_spec.md`
   - Security: `docs/PRD/07_Security/security.md`

2. **Follow naming conventions** — See `docs/PRD/02_Naming_Patterns/guidelines.md`
   - Vue components: `PascalCase` (e.g., `InstitutionCard.vue`)
   - PHP classes: `PascalCase` (e.g., `InstitutionController.php`)
   - Database tables: `snake_case` plural (e.g., `institutions`)
   - Database columns: `snake_case` (e.g., `institution_id`)
   - Routes: `kebab-case` (e.g., `/institutions/search`)
   - Methods/functions: `camelCase` (e.g., `getInstitutions()`)

3. **Implement security requirements** — See `docs/PRD/07_Security/security.md`
   - Input validation on all forms
   - CSRF protection
   - File upload security (mime type, size validation)
   - SQL injection prevention (use Eloquent ORM)
   - XSS prevention (Vue/Blade auto-escaping)

### Common Commands

```bash
# Run tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Generate model + migration
php artisan make:model Institution -m

# Generate controller
php artisan make:controller InstitutionController

# Run Laravel Tinker (interactive shell)
php artisan tinker

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# View migration status
php artisan migrate:status
```

## Project Structure

```
sedekah-info/
├── app/                       # Laravel application code
│   ├── Http/Controllers/      # Route handlers
│   ├── Http/Requests/         # Form request validation
│   ├── Http/Middleware/       # Custom middleware
│   ├── Models/                # Eloquent models
│   └── Notifications/         # Email notifications
├── resources/
│   ├── js/
│   │   ├── Components/        # Vue components
│   │   ├── Pages/             # Page components (Inertia)
│   │   ├── Layouts/           # Layout components
│   │   └── Composables/       # Vue composables
│   └── css/                   # Tailwind CSS
├── routes/
│   ├── web.php                # Web routes (Inertia)
│   └── api.php                # API routes
├── database/
│   ├── migrations/            # Database migrations
│   ├── factories/             # Model factories for testing
│   └── seeders/               # Database seeders
├── tests/
│   ├── Feature/               # Feature tests
│   └── Unit/                  # Unit tests
├── docs/
│   ├── PRD/                   # Product requirements (READ-ONLY)
│   ├── GUIDES/                # Implementation guides (generated)
│   └── DEPLOYMENT/            # Infrastructure docs (generated)
├── docker/                    # Docker configuration
├── k8s/                       # Kubernetes manifests
└── CLAUDE.md                  # Development workflow guide
```

## API Documentation

The API uses RESTful conventions with JSON responses. Base URL: `https://sedekah.info/api/v1`

### Public Endpoints (No Auth Required)

```
GET    /institutions              List institutions with filters
GET    /institutions/{id}         Get institution details
GET    /institutions/slug/{slug}  Get by slug
GET    /payment-methods           List available payment methods
GET    /institutions/search?q=    Search institutions
```

### Admin Endpoints (Requires Auth)

```
POST   /admin/institutions        Create institution
PUT    /admin/institutions/{id}   Update institution
DELETE /admin/institutions/{id}   Delete institution
POST   /admin/institutions/{id}/qr-codes           Upload QR code
PATCH  /admin/qr-codes/{qr_id}    Update QR status
GET    /admin/submissions         List pending submissions
POST   /admin/submissions/{id}/approve   Approve submission
POST   /admin/submissions/{id}/reject    Reject submission
GET    /admin/dashboard/analytics        Dashboard analytics
```

Full API documentation: `docs/PRD/05_Backend/api_spec.md`

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/InstitutionTest.php

# Run with coverage report
php artisan test --coverage

# Run with specific filter
php artisan test --filter=test_user_can_browse_institutions
```

**Target Coverage**: 70%+

## Deployment

### Docker (Development)

```bash
docker-compose up -d
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### Kubernetes (Production)

```bash
kubectl apply -f k8s/
kubectl rollout status deployment/sedekah-app -n sedekah
```

See `docs/DEPLOYMENT/` for detailed infrastructure setup.

## Security

### Before Launch
- ✅ HTTPS enabled (Let's Encrypt)
- ✅ CSRF protection on all forms
- ✅ Input validation and sanitization
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS prevention (template escaping)
- ✅ Rate limiting enabled
- ✅ File upload security
- ✅ Security headers configured
- ✅ Dependencies audited (`composer audit`)

### Reporting Security Issues

Please email security concerns to `security@sedekah.info` rather than opening public issues.

## Contributing

We welcome contributions! Please:

1. Read `docs/PRD/00_DEVELOPMENT_REQUIREMENTS.md` for requirements
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Follow the naming conventions in `docs/PRD/02_Naming_Patterns/guidelines.md`
4. Write tests for new functionality
5. Ensure `php artisan test` passes
6. Commit with clear messages referencing relevant PRD sections
7. Push and create a pull request

## License

This project is open-source software licensed under the [MIT license](LICENSE).

## Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/sedekah-info/issues)
- **Email**: support@sedekah.info
- **Documentation**: `docs/PRD/`

## Acknowledgments

Sedekah.info is built on:
- [Laravel](https://laravel.com) — Web framework
- [Vue 3](https://vuejs.org) — Frontend framework
- [Inertia.js](https://inertiajs.com) — Server-side rendering
- [Tailwind CSS](https://tailwindcss.com) — Styling
- [Cloudflare](https://www.cloudflare.com) — CDN & R2 storage

---

**Status**: MVP Development
**Last Updated**: 2026-02-22
**Version**: 1.0.0-alpha
