# Git Setup & Team Collaboration Guide

## 🚀 Setting Up Remote Repository

### Step 1: Create Remote Repository

Choose one of these platforms:

#### Option A: GitHub
1. Go to [github.com](https://github.com) and login
2. Click **+** → **New repository**
3. Name: `fitmate`
4. Description: `FitMate - Fitness Application with Laravel, Vue, and Inertia`
5. Choose **Private** (for team projects) or **Public**
6. Do NOT initialize with README (we already have one)
7. Click **Create repository**

#### Option B: GitLab
1. Go to [gitlab.com](https://gitlab.com) and login
2. Click **New project**
3. Create blank project
4. Name: `fitmate`
5. Do NOT initialize with README
6. Click **Create project**

#### Option C: Bitbucket
1. Go to [bitbucket.org](https://bitbucket.org) and login
2. Click **+** → **Create repository**
3. Repository name: `fitmate`
4. Do NOT initialize with README
5. Click **Create**

### Step 2: Add Remote to Local Repository

After creating the remote repository, you'll get a URL. Run:

```bash
cd "C:\Study\CSE471 Project\fitmate"

# Add remote (replace URL with your actual repository URL)
git remote add origin https://github.com/yourname/fitmate.git

# Or if using SSH (requires SSH key setup):
git remote add origin git@github.com:yourname/fitmate.git

# Verify remote was added
git remote -v
```

You should see:
```
origin  https://github.com/yourname/fitmate.git (fetch)
origin  https://github.com/yourname/fitmate.git (push)
```

### Step 3: Push Initial Commit

```bash
# Rename branch from 'main' to 'main' (GitHub default)
git branch -M main

# Push to remote
git push -u origin main
```

The `-u` flag sets `origin/main` as the default upstream, so future `git push` commands work without specifying the branch.

---

## 👥 Team Members Setup

### For Team Members (Cloning for First Time)

```bash
# Clone the repository
git clone https://github.com/yourname/fitmate.git
cd fitmate

# Install PHP dependencies
composer install

# Install Node dependencies
npm install --legacy-peer-deps

# Setup environment file
cp .env.example .env

# Generate Laravel key
php artisan key:generate

# Create database and run migrations
php artisan migrate

# Build frontend assets
npm run build

# Done! Ready to work 🎉
```

### For Team Members (Daily Workflow)

**Before starting work each day:**
```bash
# Get latest changes
git fetch origin
git pull origin main
```

**During development:**
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Frontend build in watch mode
npm run dev
```

**When done with feature:**
```bash
# See what changed
git status

# Stage changes
git add .

# Commit with clear message
git commit -m "feat: add workout tracking

- Created workout migration
- Added WorkoutController
- Built Workout.vue component"

# Push to remote
git push origin feature/your-feature-name

# Create Pull Request on GitHub/GitLab
```

---

## 🔀 Complete Git Workflow for Collaboration

### Branch Strategy: Git Flow

```
main (production-ready)
  ↑
  └─ develop (integration branch)
       ↑
       ├─ feature/workout-tracking
       ├─ feature/meal-planning
       ├─ bugfix/login-issue
       └─ docs/setup-guide
```

### Setup Git Flow

**Create and track develop branch:**
```bash
git checkout -b develop
git push -u origin develop
```

**Configure default branch** on GitHub/GitLab:
1. Go to repository Settings
2. Find "Default branch" or "Default reference"
3. Change from `main` to `develop`
4. Save

### Creating Features from Develop

```bash
# Get latest develop
git checkout develop
git pull origin develop

# Create feature branch from develop
git checkout -b feature/my-feature

# Make changes...

# Commit
git add .
git commit -m "feat: my feature"

# Push feature branch
git push -u origin feature/my-feature
```

### Creating Pull Requests

1. **Go to GitHub/GitLab website**
2. **Create Pull Request:**
   - From: `feature/my-feature`
   - To: `develop` (NOT main!)
3. **Fill in details:**
   ```
   Title: feat: add workout tracking
   
   Description:
   ## What does this PR do?
   Adds ability to track user workouts
   
   ## Changes
   - Created WorkoutController with CRUD
   - Added Workout model and migration
   - Built responsive Workout.vue page
   
   ## Testing
   1. Login to dashboard
   2. Click Workout in navbar
   3. Add new workout - should appear in list
   4. Edit/delete workout - should update
   
   Closes #123
   ```
4. **Request reviewers** from team
5. **Wait for approval** before merging

### Code Review Process

**For Reviewer:**
1. Review code changes
2. Request changes if needed
3. Approve when ready
4. Merge PR to develop

**For Developer:**
1. Address review comments
2. Push additional commits
3. Request re-review
4. Get approval
5. Merge to develop

### Release to Main

Only merge to `main` when develop is stable:

```bash
# Create release PR
git checkout main
git pull origin main
git merge develop
git push origin main

# Tag release version
git tag v1.0.0
git push origin v1.0.0
```

---

## 🔑 Authentication Methods

### HTTPS (Easier, Requires Password Each Time)
```bash
git remote add origin https://github.com/username/repo.git
```
You'll enter your GitHub/GitLab username and password (or personal access token).

### SSH (More Secure, Setup Once)

**Generate SSH key:**
```bash
ssh-keygen -t ed25519 -C "your.email@example.com"
# Press Enter to accept defaults
```

**Add public key to GitHub:**
1. Copy: `cat ~/.ssh/id_ed25519.pub`
2. Go to GitHub Settings → SSH and GPG keys
3. Click **New SSH key**
4. Paste and save

**Use SSH remote:**
```bash
git remote add origin git@github.com:username/repo.git
```

---

## 📊 Useful Git Commands

```bash
# View commit history
git log --oneline                    # Short history
git log --graph --all --decorate    # Visual branch graph

# See what changed
git diff                            # Unstaged changes
git diff --staged                   # Staged changes
git diff main feature/branch        # Compare branches

# Undo changes
git restore <file>                  # Discard changes in file
git restore --staged <file>         # Unstage file
git reset --soft HEAD~1             # Undo last commit, keep changes

# Stash (save work temporarily)
git stash                           # Save current changes
git stash pop                       # Restore saved changes
git stash list                      # View all stashes

# Sync with remote
git fetch origin                    # Download remote changes
git pull origin develop             # Download and merge
git push origin feature/branch      # Upload branch

# Branch management
git branch                          # List local branches
git branch -a                       # List all branches (local + remote)
git branch -d feature/old           # Delete local branch
git push origin --delete feature/old # Delete remote branch
```

---

## 🚨 Common Scenarios

### I pushed to wrong branch!
```bash
# Find the commit hash you want to undo
git log --oneline

# Reset the branch
git reset --hard HEAD~1  # Undo last commit
git push origin branch --force-with-lease
```

### I want to delete my local changes
```bash
git restore .  # Discard all changes
# or
git checkout -- .  # Alternative syntax
```

### I need to sync my branch with main
```bash
git fetch origin
git rebase origin/main
# or use merge if rebase is complicated
git merge origin/main
```

### Someone merged my PR, now I have conflicts
```bash
git pull origin develop
# Edit files to resolve conflicts (look for <<<<, ====, >>>>)
git add .
git commit -m "resolve: merge conflicts with develop"
```

### I accidentally committed sensitive data
```bash
# Remove file from git history (use with caution!)
git rm --cached .env
echo ".env" >> .gitignore
git commit -m "chore: remove .env from tracking"
git push origin branch
```

---

## ✅ Team Best Practices

### DO ✅
- Pull before starting work
- Create feature branches for each change
- Write clear commit messages
- Request code reviews
- Test before pushing
- Keep commits atomic (one feature per commit)
- Use meaningful branch names

### DON'T ❌
- Push directly to `main`
- Commit `.env` file
- Make huge commits with many unrelated changes
- Force push to shared branches (main, develop)
- Leave work uncommitted at end of day
- Ignore code review feedback

---

## 🆘 Troubleshooting

### Remote rejected (need pull first)
```bash
git pull origin branch
git push origin branch
```

### Can't push - need SSH key setup
```bash
# Setup SSH (see "SSH Authentication" section above)
git remote set-url origin git@github.com:username/repo.git
git push origin branch
```

### Need to undo a commit that's already pushed
```bash
# Create a new commit that reverts changes
git revert <commit-hash>
git push origin branch
```

---

## 📚 Additional Resources

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [Atlassian Git Tutorials](https://www.atlassian.com/git/tutorials)
- [Git Branching Model](https://nvie.com/posts/a-successful-git-branching-model/)

---

## 🎯 Next Steps

1. **Create remote repository** on GitHub/GitLab/Bitbucket
2. **Run Step 2 & 3** above to push to remote
3. **Share repository URL** with team members
4. **Team members follow** "Team Members Setup" section
5. **Start collaborating!** using the workflow described

---

**Ready to go!** 🚀 Share the repository URL with your team members.
