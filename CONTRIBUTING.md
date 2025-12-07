# Contributing to FitMate

This guide will help you set up your development environment and contribute to the FitMate project.

## 📋 Prerequisites

- **PHP 8.2+** - Check with `php --version`
- **Node.js 18+** - Check with `node --version`
- **Composer** - PHP dependency manager
- **Git** - Version control
- **SQLite** - Database (included with PHP)

## 🚀 Initial Setup for New Contributors

### 1. Clone the Repository

```bash
git clone <repository-url> fitmate
cd fitmate
```

Replace `<repository-url>` with your actual repository URL (e.g., `https://github.com/yourname/fitmate.git`)

### 2. Install PHP Dependencies

```bash
composer install
```

This installs all Laravel packages specified in `composer.json`.

### 3. Create Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Google OAuth (Optional)

If you want to test Google OAuth login:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials (Web application)
5. Set redirect URI to: `http://127.0.0.1:8000/auth/google/callback`
6. Update these in `.env`:

```
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
```

See [OAUTH_SETUP.md](./OAUTH_SETUP.md) for detailed instructions.

### 5. Setup Database

```bash
# Run migrations to create database schema
php artisan migrate

# (Optional) Seed with test data
php artisan db:seed
```

### 6. Install Node Dependencies

```bash
npm install --legacy-peer-deps
```

The `--legacy-peer-deps` flag is necessary due to peer dependency conflicts with Vite 7.

### 7. Start Development Servers

**Terminal 1 - Start Laravel development server:**
```bash
php artisan serve
```

This starts the backend at `http://127.0.0.1:8000`

**Terminal 2 - Build frontend assets (watch mode):**
```bash
npm run dev
```

This watches Vue/CSS changes and rebuilds automatically. Access the app at the URL shown in Terminal 1.

## 📁 Project Structure

```
fitmate/
├── app/                    # Laravel application code
│   ├── Http/Controllers/  # Request handlers
│   ├── Models/            # Database models
│   └── ...
├── resources/
│   ├── js/
│   │   ├── Components/    # Vue components
│   │   ├── Layouts/       # Page layouts
│   │   ├── Pages/         # Page components
│   │   └── app.js         # Entry point
│   └── css/
├── routes/                # Application routes
│   ├── web.php           # Web routes
│   └── auth.php          # Auth routes
├── database/
│   ├── migrations/       # Database schema changes
│   ├── seeders/          # Test data
│   └── database.sqlite   # SQLite file
├── public/               # Publicly accessible files
│   └── build/           # Compiled assets (auto-generated)
├── .env                 # Environment configuration (⚠️ Never commit)
├── .env.example         # Example config for new contributors
├── composer.json        # PHP dependencies
├── package.json         # Node.js dependencies
└── vite.config.js       # Vite bundler configuration
```

## 🔄 Git Workflow

### Branch Naming Convention

Create branches using this format:
- `feature/feature-name` - New features
- `bugfix/bug-name` - Bug fixes
- `docs/documentation-name` - Documentation updates
- `refactor/refactor-name` - Code refactoring

Example:
```bash
git checkout -b feature/workout-tracking
```

### Making Changes

1. **Create a new branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** in the code

3. **Build frontend if you changed Vue/CSS:**
   ```bash
   npm run build
   ```

4. **Test your changes** locally

5. **Stage your changes:**
   ```bash
   git add .
   ```

6. **Commit with clear messages:**
   ```bash
   git commit -m "feat: add workout tracking feature

   - Added WorkoutTracker component
   - Created workout migration
   - Implemented API endpoints
   
   Closes #123"
   ```

   **Commit message format:** `type: short description`
   - `feat:` - New feature
   - `fix:` - Bug fix
   - `docs:` - Documentation
   - `style:` - Code style (formatting, semicolons, etc.)
   - `refactor:` - Code refactoring
   - `test:` - Adding tests
   - `chore:` - Build, dependencies, etc.

7. **Push to remote:**
   ```bash
   git push origin feature/your-feature-name
   ```

8. **Create a Pull Request** on GitHub/GitLab with:
   - Clear description of changes
   - Reference to related issues (#123)
   - Screenshots for UI changes
   - Testing instructions

### Syncing with Main Branch

Before starting new work, update your local code:

```bash
git fetch origin
git checkout main
git pull origin main
```

If you're on a feature branch and main has updated:

```bash
git fetch origin
git rebase origin/main
```

Or use merge if rebase conflicts are complicated:

```bash
git fetch origin
git merge origin/main
```

## ⚙️ Development Tips

### Hot Reload During Development

Both servers support hot reload:
- **Backend changes** - Auto-reload (php artisan serve watches files)
- **Vue/CSS changes** - Auto-rebuild (npm run dev with Vite)

Just edit files and refresh your browser!

### Database Migrations

If you need to modify the database schema:

```bash
# Create a new migration
php artisan make:migration add_new_column_to_users_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database (⚠️ Deletes all data)
php artisan migrate:reset
```

### Creating Controllers

```bash
php artisan make:controller ControllerName
php artisan make:controller Admin/DashboardController  # In subdirectory
```

### Creating Models

```bash
php artisan make:model ModelName
php artisan make:model ModelName -m  # With migration
```

### Clearing Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🧪 Testing

Run tests with:

```bash
php artisan test
```

## 📝 Important Files to NOT Commit

These are automatically ignored by `.gitignore`:
- `.env` - Local environment configuration
- `vendor/` - PHP dependencies
- `node_modules/` - Node dependencies
- `public/build/` - Compiled assets
- `storage/logs/` - Application logs
- `database.sqlite` - Local database

**Never push `.env` file** - Only `.env.example` should be in the repository.

## 🐛 Common Issues

### Port Already in Use
```bash
# Laravel (change port)
php artisan serve --port=8001

# Find process using port 8000
netstat -ano | findstr :8000
```

### npm Legacy Peer Deps Error
```bash
npm install --legacy-peer-deps
```

### Vite Manifest Not Found
```bash
npm run build
```

### Database Locked
```bash
# Reset database
php artisan migrate:reset
php artisan migrate
```

### Git Merge Conflicts
Edit the file, resolve conflicts (look for `<<<<`, `====`, `>>>>`), then:
```bash
git add .
git commit -m "resolve: merge conflicts"
```

## 📞 Getting Help

- Check existing issues and discussions
- Ask in team communication channels
- Review similar code in the project
- Check [Laravel docs](https://laravel.com/docs)
- Check [Vue 3 docs](https://vuejs.org/)
- Check [Inertia.js docs](https://inertiajs.com/)

## 🎯 Code Standards

- **PHP**: Follow PSR-12 standards
- **JavaScript/Vue**: Use consistent formatting (Prettier configured)
- **Database**: Use migrations for all schema changes
- **Controllers**: Keep logic thin, use Models for business logic
- **Components**: Keep Vue components focused and reusable

Happy coding! 🚀
