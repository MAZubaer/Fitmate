# FitMate - Quick Start Guide for Team Members

## ⚡ 5-Minute Setup

### First Time Only (5 min)
```bash
# 1. Clone repo
git clone <repo-url>
cd fitmate

# 2. Install dependencies
composer install
npm install --legacy-peer-deps

# 3. Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate

# Done! 🎉
```

### Start Developing (Every time)
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Then open: **http://127.0.0.1:8000**

---

## 📝 Making Changes

### Step 1: Create Feature Branch
```bash
git checkout -b feature/my-feature-name
```

### Step 2: Make Changes
Edit files, build assets if needed:
```bash
npm run build  # If you changed Vue/CSS
```

### Step 3: Commit & Push
```bash
git add .
git commit -m "feat: describe your change"
git push origin feature/my-feature-name
```

### Step 4: Create Pull Request
- Go to GitHub/GitLab
- Click "New Pull Request"
- Describe what you changed and why
- Request review from team

---

## 🔧 Useful Commands

| Task | Command |
|------|---------|
| Start dev servers | `php artisan serve` + `npm run dev` |
| Build for production | `npm run build` |
| Run tests | `php artisan test` |
| Create migration | `php artisan make:migration name` |
| Create controller | `php artisan make:controller Name` |
| Reset database | `php artisan migrate:reset && php artisan migrate` |
| Update code from main | `git fetch origin && git rebase origin/main` |
| View logs | `tail -f storage/logs/laravel.log` |

---

## 🗂️ Where to Add Code

| What | Where |
|------|-------|
| **New API endpoint** | `app/Http/Controllers/` |
| **Database table** | `database/migrations/` + Create Model |
| **Vue component** | `resources/js/Components/` |
| **New page** | `resources/js/Pages/` |
| **Business logic** | `app/Models/` (Model methods) |
| **Database model** | `app/Models/` |
| **Route** | `routes/web.php` or `routes/auth.php` |

---

## 🎨 Project Stack

- **Backend**: Laravel 12 (PHP)
- **Frontend**: Vue 3 + Inertia.js
- **Styling**: Tailwind CSS
- **Bundler**: Vite
- **Database**: SQLite
- **Auth**: Laravel Breeze + Google OAuth

---

## 🚀 Current Features

✅ User authentication (email/password)  
✅ Google OAuth login  
✅ Dashboard with stats  
✅ Navigation bar  
✅ Page stubs (Workout, Meal, Notifications)  

## 📋 TODO Features

- [ ] Workout tracking
- [ ] Meal planning assistant
- [ ] Notifications system
- [ ] User settings/profile
- [ ] Progress charts

---

## ⚠️ Important Rules

1. **Never commit `.env`** - Use `.env.example` as template
2. **Always pull before starting work** - Avoid conflicts
3. **Create feature branches** - Don't push to `main` directly
4. **Test locally first** - Before creating PR
5. **Use clear commit messages** - Make history readable

---

## 🆘 Stuck?

1. Check **CONTRIBUTING.md** for detailed guide
2. Check **README.md** for project info
3. Check **OAUTH_SETUP.md** for OAuth details
4. Ask team members! 🤝

---

**Need the full detailed guide?** → Read [CONTRIBUTING.md](./CONTRIBUTING.md)
