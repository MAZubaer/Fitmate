# 📋 Git Quick Reference Card

## 🚀 For You: Setup Remote & Push Code

```bash
# 1. Create remote repo on GitHub/GitLab/Bitbucket (copy URL from there)
# 2. Add remote to local repo
git remote add origin https://github.com/yourname/fitmate.git

# 3. Push to remote
git push -u origin main

# Verify it worked
git remote -v
```

---

## 👥 For Team Members: Clone & Setup (One Time)

```bash
# Clone repo
git clone https://github.com/yourname/fitmate.git
cd fitmate

# Install dependencies
composer install
npm install --legacy-peer-deps

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate

# Ready to code!
```

---

## 💻 Daily Workflow for Team Members

```bash
# Morning: Get latest code
git fetch origin
git pull origin develop

# Start coding on feature branch
git checkout -b feature/my-feature

# Terminal 1: Backend server
php artisan serve

# Terminal 2: Frontend build
npm run dev

# Make changes, then save them
git add .
git commit -m "feat: description of what you added"
git push -u origin feature/my-feature

# Create Pull Request on GitHub/GitLab
# → Go to website, click "Compare & pull request"
# → Choose: from feature/my-feature into develop
# → Request review from team
# → Wait for approval
# → Merge to develop
```

---

## 🔧 Essential Commands

### Checking Status
```bash
git status              # What changed?
git log --oneline       # Recent commits
git branch -a           # All branches
```

### Making Changes
```bash
git add .               # Stage all changes
git add file.php        # Stage one file
git commit -m "msg"     # Commit with message
git push origin branch  # Upload to remote
```

### Getting Latest Code
```bash
git fetch origin        # Download changes (don't merge yet)
git pull origin develop # Download + merge
git rebase origin/main  # Rebase changes on top of main
```

### Undoing Mistakes
```bash
git restore .                   # Discard all changes
git restore file.php            # Discard changes in one file
git restore --staged file.php   # Unstage file
git reset --soft HEAD~1         # Undo last commit (keep changes)
git revert <commit-hash>        # Create commit that undoes a commit
```

### Branching
```bash
git branch feature/name         # Create branch
git checkout feature/name       # Switch to branch
git checkout -b feature/name    # Create and switch
git branch -d feature/name      # Delete branch (local)
git push origin --delete name   # Delete branch (remote)
git merge feature/name          # Merge branch into current
```

---

## 📊 Git Workflow Diagram

```
Remote (GitHub/GitLab/Bitbucket)
    ↓ git push origin feature/name
Local Repository
    ↓ git add .
    ↓ git commit -m "message"
Working Directory (your files)
    ↓ make changes
Your Code Editor
```

---

## 🎯 Branching Strategy

```
main (production)
  ↑ (only merge when stable)
  └─ develop (staging)
       ↑ (merge feature PRs here)
       ├─ feature/workout (your work)
       ├─ feature/meals    (someone's work)
       └─ bugfix/login     (someone's work)
```

**Rules:**
- Always branch from `develop` (except hotfixes from `main`)
- Never push directly to `main` or `develop`
- Always create Pull Request for review
- Merge through Pull Request, not locally

---

## ❓ Quick Troubleshooting

### "I want to download latest changes"
```bash
git pull origin develop
```

### "I want to see what I changed"
```bash
git diff
```

### "I want to undo my last commit"
```bash
git reset --soft HEAD~1
```

### "I made a commit to wrong branch"
```bash
git reset --soft HEAD~1           # Undo commit
git checkout -b feature/correct   # Go to correct branch
git commit -m "message"           # Commit again
```

### "I have merge conflicts"
```bash
# Edit files to resolve conflicts (look for <<<<, ====, >>>>)
git add .
git commit -m "resolve: merge conflicts"
```

### "I want to throw away all my changes"
```bash
git restore .
```

---

## 📝 Commit Message Format

```
type: short description (under 50 chars)

Optional longer description explaining:
- What changed
- Why it changed
- Any side effects

Optional reference to issue:
Closes #123
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation
- `style:` Formatting
- `refactor:` Code cleanup
- `test:` Tests
- `chore:` Build/deps

**Examples:**
```
feat: add workout tracking page
fix: prevent user login after logout
docs: add setup instructions
refactor: simplify database queries
```

---

## ✅ Before You Push

- [ ] Code runs locally without errors
- [ ] No console errors in browser
- [ ] No database errors
- [ ] Follow code style of project
- [ ] Tested the feature/fix
- [ ] Commit message is clear
- [ ] All files saved (`git status` shows clean)

---

## 🚫 Never Do

```bash
git push --force              # ❌ Can break team's work
git commit -m "asdf"          # ❌ Bad message
git add .env                  # ❌ Config file (use .env.example)
git push to main directly     # ❌ Always use Pull Request
```

---

## 📚 Full Documentation

- **Quick Setup**: `TEAM_SETUP.md`
- **Contributing Guide**: `CONTRIBUTING.md`
- **Git Workflow**: `GIT_WORKFLOW.md`
- **Team Collaboration**: `TEAM_COLLABORATION.md`

---

**Print this card or bookmark it!** 📌
