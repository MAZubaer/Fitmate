<p align="center">
  <img src="public/images/fitmate-logo.png" alt="FitMate logo" width="160">
</p>

<h1 align="center">FitMate</h1>

<p align="center">
  <b>Your all-in-one fitness companion.</b><br>
  Plan workouts, log meals, track BMI, steps, water and mood, get reminders, export reports,<br>
  and chat with an AI coach that knows your real fitness data.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue-3-4FC08D?logo=vuedotjs&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Inertia.js-2-9553E9" alt="Inertia 2">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white" alt="Vite 7">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/PostgreSQL%20%7C%20SQLite-336791?logo=postgresql&logoColor=white" alt="PostgreSQL / SQLite">
  <img src="https://img.shields.io/badge/Docker-Render-2496ED?logo=docker&logoColor=white" alt="Docker on Render">
</p>

---

## Table of Contents

1. [Overview](#1-overview)
2. [Feature Walkthrough](#2-feature-walkthrough)
   - [Landing Page](#21-landing-page)
   - [Authentication](#22-authentication)
   - [Dashboard](#23-dashboard)
   - [Workout Tracker](#24-workout-tracker)
   - [Meal Tracker](#25-meal-tracker)
   - [AI Coach (Meal Assistant)](#26-ai-coach-meal-assistant)
   - [Notifications & Reminders](#27-notifications--reminders)
   - [Analytics Export](#28-analytics-export)
   - [Profile](#29-profile)
3. [Business Rules](#3-business-rules)
4. [Architecture](#4-architecture)
5. [Tech Stack](#5-tech-stack)
6. [Project Structure](#6-project-structure)
7. [Database Schema](#7-database-schema)
8. [API & Route Reference](#8-api--route-reference)
9. [External Services](#9-external-services)
10. [Security](#10-security)
11. [Getting Started](#11-getting-started)
12. [Configuration (Environment Variables)](#12-configuration-environment-variables)
13. [Running the Scheduler](#13-running-the-scheduler)
14. [Testing & Code Style](#14-testing--code-style)
15. [Deployment](#15-deployment)
16. [Troubleshooting](#16-troubleshooting)
17. [Known Limitations](#17-known-limitations)
18. [Contributing](#18-contributing)
19. [License](#19-license)

---

## 1. Overview

**FitMate** is a full-stack fitness tracking web application. It is a single Laravel 12 codebase that serves a **Vue 3** single-page interface through **Inertia.js**, so there is no separate frontend project or REST API to maintain.

What a user can do:

| Area | What it does |
| ---- | ------------ |
| 🏋️ **Workouts** | Build a workout plan, schedule exercises with date and time, mark them complete and review history by muscle group |
| 🍽️ **Meals** | Keep a meal diary with calories and meal times; earn points and streaks for logging |
| ❤️ **Health metrics** | Log BMI, steps, water intake and daily mood, each with a history chart |
| 📊 **Dashboard** | See calories in vs. out, the daily calorie goal, weekly progress, upcoming workouts and live weather |
| 🤖 **AI coach** | Chat with "FitMate", an AI fitness coach that reads your recent meals, workouts, BMI, steps and water |
| 🔔 **Reminders** | Get email and in-app reminders when a scheduled meal or workout is due |
| 📁 **Reports** | Download your health data for any date range as CSV or PDF |

The UI is fully responsive (desktop, tablet and phone), with a collapsible mobile navigation menu.

---

## 2. Feature Walkthrough

### 2.1 Landing Page

`/` (`resources/js/Pages/Welcome.vue`)

- Hero section with the FitMate logo and rotating motivational quotes (e.g. *"The body achieves what the mind believes."*)
- **Login** and **Register** call-to-action buttons

### 2.2 Authentication

Built on **Laravel Breeze** (Vue + Inertia stack) and extended with **Google OAuth**.

| Feature | Details |
| ------- | ------- |
| Register | Name, email, password + confirmation (Laravel default password rules). The user is logged in and redirected to the dashboard. |
| Login | Email + password with a "Remember me" option |
| **Sign in / Sign up with Google** | Uses Laravel Socialite. On callback FitMate: (1) looks up the user by `google_id`; (2) otherwise links the Google account to an existing user with the same email; (3) otherwise creates a new user with a verified email. Google access and refresh tokens are stored on the user. The user is logged in with "remember me" turned on. |
| Forgot / reset password | Emailed reset link |
| Email verification | Verification screen and resend link (throttled to 6 requests per minute) |
| Confirm password | Required before sensitive actions |
| Logout | `POST /logout` |

Accounts created through Google have no password (`users.password` is nullable).

### 2.3 Dashboard

`/dashboard` (`DashboardController@index` → `resources/js/Pages/Dashboard.vue`)

The dashboard is the app's home screen. It combines server-rendered props with data that widgets load themselves through JSON requests.

**Header & profile card**
- Greeting: *"Welcome back, {nickname or name}! 💪"* and the "Member since" date
- Age and gender from the profile
- Quick stats: 💧 today's water (ml), 🚶 today's steps, 🔥 meal streak (days), ⭐ meal points

**Mood check-in**
- One tap to record today's mood: 😄 **Happy**, 😐 **Neutral** or 😞 **Stressed**
- Stored once per day; tapping again changes today's value

**Weather widget**
- Current temperature, condition (emoji icon) and humidity for **Dhaka, BD**, fetched in the browser from the free [Open-Meteo](https://open-meteo.com) API. No API key is needed.

**Calorie stats**
- Calories eaten **this week** (last 7 days) and **today**
- **Daily calorie goal** (default 2,200 kcal) with a percentage progress bar
- A modal to change the goal (500–10,000 kcal)
- Weekly goals completed: the number of days in the last 7 where intake reached the goal

**Upcoming activity**
- Workouts scheduled for today and tomorrow (`/recent-activity`), showing date, time, sets and reps
- An empty state ("No activity yet – Start logging your workouts!")

**Charts** (Chart.js via `vue-chartjs`)
| Chart | Source |
| ----- | ------ |
| Calories Burned (last 7 days) | Sum of `calories` from **completed** workouts per day |
| Calorie Intake (last 7 days) | Sum of meal `calories` per day |
| Step History | `/steps-history` |
| Water Intake | `/water-history` |
| BMI History | `/bmi-history` |

Days without data are filled with `0`, so the 7-day charts always show seven bars. Weekday labels are formatted in the `Asia/Dhaka` timezone with `date-fns-tz`.

**Quick-entry modals**
- **Update BMI**: enter height (m) and weight (kg). The BMI is calculated live in the browser with its category and an encouraging message (see [BMI rules](#bmi)). Limited to one entry per day.
- **Update Steps**: adds steps to today's total
- **Update Water Intake**: adds millilitres to today's total

### 2.4 Workout Tracker

`/workout` (`resources/js/Pages/Workout.vue`; data via `/workouts-data`)

**Adding a new workout plan**
- Form fields: exercise name, sets, reps, date, time, calories, duration (seconds) and **muscle group** (Chest, Back, Shoulders, Arms, Legs, Abs/Core, Full Body)
- **Add single workout**: saves it right away
- **Plan builder**: add several exercises to a *current plan*, review them, then **Save Plan** (saves each one) or **Clear Plan**

**To-do list of my workouts**
- Every workout you've scheduled, newest first
- **Search** by exercise name
- Each card shows badges for sets, reps, date and time, calories, duration and muscle group
- Actions: ✏️ **Edit** inline, 🗑️ **Delete**, ✅ **Mark as completed**

**History (completed workouts)**
- Lists only completed workouts
- **Calculate Total Calories Burned** across completed workouts
- Total duration (seconds)
- **Muscle-group summary**: how many completed sessions targeted each muscle group

Saving a workout also creates an in-app notification for its scheduled date and time. Only completed workouts count toward the dashboard's "Calories Burned" chart.

### 2.5 Meal Tracker

`/meals` (Laravel resource controller `MealController` → `Pages/Meals/*`)

| Page | Description |
| ---- | ----------- |
| **Index** (`/meals`) | All your meals, newest date first, with calories and Edit/Delete buttons |
| **Create** (`/meals/create`) | Name (required), description, calories, date (defaults to today), time |
| **Edit** (`/meals/{id}/edit`) | Update any field |

**Gamification**
- ⭐ **+10 points** for every meal logged
- 🔥 **Streak**: grows by 1 when you log a meal the day after your last logged day, and resets to 1 otherwise
- Deleting a meal **removes 10 points** (never below 0) and **recalculates** the streak from your remaining meal dates

If a meal has a time set, an in-app notification is created for that date and time, and the scheduler sends an email reminder when it's due.

### 2.6 AI Coach (Meal Assistant)

`/meal-assistant` (`resources/js/Pages/MealAssistant.vue` + `AiMealAssistantController`)

A ChatGPT-style chat screen titled **"FitMate AI Coach – Your personal fitness assistant"**.

**How a message is handled**
1. The browser sends `{ prompt, session_id }` to `POST /ai/meal-assistant` (prompt up to 1,000 characters)
2. The server builds a **fitness profile** from the user's real data:
   - Latest BMI
   - Last 5 meals (name, kcal, date)
   - Last 5 workouts (name, kcal burned, date)
   - Latest step and water records
3. It calls the **Groq Chat Completions API** (OpenAI-compatible) with model `openai/gpt-oss-20b` and temperature `0.7`, sending:
   - a system prompt that defines FitMate as a professional fitness coach (workouts, nutrition, hydration, recovery, health)
   - the fitness profile as a second system message
   - the user's prompt
4. The reply is saved to `ai_chats` with the session UUID and returned to the browser
5. The browser renders the reply as **Markdown** (`marked`) and sanitises the HTML with **DOMPurify**

**Chat sessions**
- 💬 A sidebar lists past conversations (the first message of each session, newest first)
- Click a session to reload it; delete a session to remove all its messages
- The current session ID is kept in `localStorage`, so a page refresh resumes the same chat
- Starting a new chat creates a new session UUID
- Older chats saved before sessions existed are given a UUID automatically the first time the history loads

### 2.7 Notifications & Reminders

**In-app notifications** (`/notifications`)
- A list of notifications whose scheduled time has passed, newest first
- Unread items are highlighted; clicking one marks it as read
- The navbar shows a 🔔 badge with the **unread count**

**Where notifications come from**
1. When a meal (with a time) or workout is saved, a notification is created for its scheduled date and time. It only appears once that time arrives.
2. The scheduled `reminders:send` command runs every minute. It sends an **email** and creates a notification for meals and workouts that became due in the last 2 minutes (see [Running the Scheduler](#13-running-the-scheduler)).

### 2.8 Analytics Export

`/analytics/export` (`AnalyticsExportController`)

Pick a **From** and **To** date, then download:

| Format | File | Contents |
| ------ | ---- | -------- |
| **CSV** | `health_data.csv` | Columns `Date, Type, Name, Calories In, Calories Out, Value`; one row per meal, workout, step record and water record, plus the latest BMI |
| **PDF** | `health_report_{from}_to_{to}.pdf` | The same data rendered from `resources/views/exports/analytics.blade.php` with DomPDF, including the user's details and date range |

Meals are filtered by `meal_date`; workouts, steps and water by `created_at` within the range.

### 2.9 Profile

`/profile` (`ProfileController` + Breeze profile partials)

- Update **name, nickname, age (0–120), gender (male/female) and email**. Changing the email clears its verification.
- Change password
- Delete account (requires your current password). Related data is removed through cascading foreign keys.

---

## 3. Business Rules

### Calorie goal
- Stored in `users.calorie_goal`, default **2,200 kcal**
- Dashboard endpoint `POST /calorie-goal` accepts **500–10,000**
- Goal % = `round(today_calories / daily_goal × 100)`
- A day counts as "goal completed" when intake ≥ goal

### BMI
- Formula: `BMI = weight (kg) / height (m)²`, rounded to 2 decimals
- Server validation: height 0.5–3 m, weight 10–500 kg, BMI 5–100
- **One entry per user per day** (enforced in the controller and by a unique `(user_id, date)` index)

| BMI | Category |
| --- | -------- |
| < 18.5 | Underweight |
| 18.5 – 24.9 | Normal |
| 25 – 29.9 | Overweight |
| ≥ 30 | Obese |

Each category shows one of several random, supportive messages.

### Steps & water
- One row per user per day (unique `(user_id, date)`)
- Each submission **adds** to today's total (`steps`, or `amount` in ml); the minimum per entry is 1

### Mood
- Allowed values: `HAPPY`, `NEUTRAL`, `STRESSED`
- One row per user per day (`updateOrCreate`), so a later check-in replaces the earlier one

### Meal points & streak
| Event | Points | Streak |
| ----- | ------ | ------ |
| Add a meal | +10 | +1 if `last_meal_date` was yesterday, otherwise reset to 1 |
| Delete a meal | −10 (min 0) | Recounted as the number of consecutive days with meals, counting back from today |

### Time zone
The application time zone is **`Asia/Dhaka`** (`config/app.php`). "Today", reminders and chart dates all use this zone.

---

## 4. Architecture

```
┌────────────────────────── Browser ──────────────────────────┐
│ Vue 3 pages (Inertia)    axios JSON calls      Chart.js     │
│ Dashboard, Workout,      /steps, /water,       vue-chartjs  │
│ Meals, MealAssistant…    /bmi, /mood, /ai/…                 │
└──────────┬───────────────────────┬──────────────────────────┘
           │ Inertia visits        │ XHR (session cookie + CSRF)
           ▼                       ▼
┌──────────────────────── Laravel 12 ─────────────────────────┐
│ routes/web.php, routes/auth.php  →  auth middleware         │
│ Controllers ─── Eloquent Models ─── DB (SQLite / Postgres)  │
│      │                                                      │
│      ├── Groq API (AI coach)       ── Laravel HTTP client   │
│      ├── Google OAuth              ── Socialite             │
│      ├── DomPDF (PDF export)                                │
│      └── Mail (reminders, verification, password reset)     │
│ Scheduler: reminders:send (every minute)                    │
└─────────────────────────────────────────────────────────────┘
```

**Two kinds of endpoints**
1. **Inertia page routes**: the controller returns `Inertia::render('Page', props)`. The first visit loads `resources/views/app.blade.php`; later visits swap page components without a full reload.
2. **JSON endpoints**: used by dashboard widgets, the workout tracker, the AI chat and notifications through `axios`. They're protected by the same session authentication and CSRF token (configured in `resources/js/bootstrap.js`).

**Shared props**: `HandleInertiaRequests` shares `auth.user` with every page.

**Routing in JavaScript**: Ziggy exposes Laravel's named routes to Vue as `route('dashboard')`.

**Layouts**: `AppLayout` (main app shell with `Navbar.vue`), `AuthenticatedLayout` (Breeze profile pages) and `GuestLayout` (auth screens).

**Navigation**: Dashboard 📊 · Workout 🏋️ · Meals 🍛 · AI Assistant 🍽️ · Notifications 🔔 (with unread badge) · profile menu (Profile, Logout).

---

## 5. Tech Stack

| Layer | Technology | Purpose |
| ----- | ---------- | ------- |
| Language | PHP 8.2+, JavaScript (ES modules) | |
| Backend framework | **Laravel 12** | Routing, ORM, validation, auth, mail, scheduling, queues |
| Frontend framework | **Vue 3** (`<script setup>`) | UI components |
| SPA glue | **Inertia.js 2** (`inertia-laravel`, `@inertiajs/vue3`) | Server-driven SPA without a separate API |
| JS routes | **Ziggy 2** | Laravel named routes in Vue |
| Styling | **Tailwind CSS 3**, `@tailwindcss/forms`, Figtree font | Responsive utility-first styling |
| Bundler | **Vite 7**, `laravel-vite-plugin`, `@vitejs/plugin-vue` | Dev server with HMR and production builds |
| HTTP client | **axios** | JSON calls from Vue |
| Charts | **Chart.js 4**, **vue-chartjs 5** | Dashboard charts |
| Dates | **date-fns 4**, **date-fns-tz 3** | Time-zone-aware formatting |
| Markdown | **marked**, **DOMPurify** | Safe rendering of AI replies |
| Auth | **Laravel Breeze**, **Sanctum**, **Socialite** | Session auth, Google OAuth |
| AI | **Groq API** (`openai/gpt-oss-20b`) via Guzzle | AI coach |
| PDF | **barryvdh/laravel-dompdf** | PDF reports |
| Weather | **Open-Meteo** (client-side) | Dashboard weather widget |
| Database | **SQLite** (local), **PostgreSQL / Neon** (production) | |
| Sessions, cache, queue | Laravel **database** drivers | No Redis required |
| Dev tooling | Laravel Pail, Pint, Sail, Collision, PHPUnit 11, concurrently | |
| Deployment | **Docker** (multi-stage), **Render** | |

---

## 6. Project Structure

```
fitmate/
├── app/
│   ├── Console/
│   │   ├── Commands/SendReminderEmails.php   # `reminders:send`
│   │   └── Kernel.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                         # Breeze controllers + OAuthController (Google)
│   │   │   ├── AiMealAssistantController.php # AI coach (Groq)
│   │   │   ├── AnalyticsExportController.php # CSV / PDF export
│   │   │   ├── BmiController.php             # Save BMI
│   │   │   ├── BmiHistoryController.php      # BMI chart data
│   │   │   ├── DashboardController.php       # Dashboard props + calorie goal
│   │   │   ├── MealController.php            # Meal CRUD + points/streak
│   │   │   ├── MoodController.php            # Daily mood
│   │   │   ├── NotificationController.php    # Notifications JSON
│   │   │   ├── ProfileController.php         # Profile edit/update/delete
│   │   │   ├── RecentActivityController.php  # Upcoming workouts
│   │   │   ├── StepController.php            # Add steps / today's steps
│   │   │   ├── StepHistoryController.php     # Steps chart data
│   │   │   ├── WaterController.php           # Add water / today's water
│   │   │   ├── WaterHistoryController.php    # Water chart data
│   │   │   └── WorkoutController.php         # Workout CRUD + complete
│   │   ├── Middleware/HandleInertiaRequests.php
│   │   └── Requests/                         # LoginRequest, ProfileUpdateRequest
│   ├── Models/                               # AiChat, BmiHistory, DailyMood, Meal, Notification,
│   │                                         # StepHistory, User, WaterHistory, Workout
│   └── Providers/AppServiceProvider.php      # Vite prefetch + HTTPS in production
├── bootstrap/                                # app.php, providers.php
├── config/                                   # app.php (Asia/Dhaka), database.php, services.php (Google, Groq), dompdf.php…
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/                           # Full schema history
│   └── seeders/DatabaseSeeder.php
├── public/
│   ├── images/                               # fitmate-logo*.{png,jpg}, push-yourself.png
│   └── index.php
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── app.js                            # Inertia + Vue bootstrap
│   │   ├── bootstrap.js                      # axios defaults
│   │   ├── Components/                       # Navbar, Modal, Dropdown, inputs, buttons…
│   │   ├── Layouts/                          # AppLayout, AuthenticatedLayout, GuestLayout
│   │   └── Pages/
│   │       ├── Auth/                         # Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword
│   │       ├── Meals/                        # Index, Create, Edit
│   │       ├── Profile/                      # Edit + partials
│   │       ├── AnalyticsExport.vue
│   │       ├── Dashboard.vue
│   │       ├── MealAssistant.vue
│   │       ├── Notifications.vue
│   │       ├── Welcome.vue
│   │       └── Workout.vue
│   └── views/
│       ├── app.blade.php                     # Inertia root view
│       └── exports/analytics.blade.php       # PDF report template
├── routes/
│   ├── web.php                               # App routes
│   ├── auth.php                              # Auth + Google OAuth routes
│   └── console.php                           # Scheduler
├── tests/                                    # Feature (auth, profile) and Unit tests
├── Dockerfile                                # Multi-stage production image
├── composer.json / package.json
├── tailwind.config.js / postcss.config.js / vite.config.js
└── *.md                                      # Team, Git and OAuth guides
```

---

## 7. Database Schema

All user-owned tables have a `user_id` foreign key to `users.id` with **cascade delete**, plus Laravel `created_at` / `updated_at` timestamps.

### `users`
| Column | Type | Notes |
| ------ | ---- | ----- |
| id | bigint PK | |
| google_id | string, nullable, unique | Google account ID |
| google_token / google_refresh_token | text, nullable | OAuth tokens |
| name | string | |
| nickname | string, nullable | Shown in the dashboard greeting |
| age | integer, nullable | |
| gender | string, nullable | `male` / `female` |
| email | string, unique | |
| email_verified_at | timestamp, nullable | |
| password | string, **nullable** | Null for Google-only accounts |
| calorie_goal | integer, default 2200 | |
| meal_streak | integer | Consecutive days with meals |
| meal_points | integer | +10 per meal |
| last_meal_date | date, nullable | Used for streaks |
| remember_token | string | |

### `workouts`
| Column | Type | Notes |
| ------ | ---- | ----- |
| name | string | Exercise name |
| sets, reps | integer | ≥ 1 |
| date | date | Scheduled date |
| time | time | Scheduled time |
| calories | integer, default 0 | Calories burned |
| duration | integer, nullable | Seconds |
| muscle_group | string, nullable | Chest, Back, Shoulders, Arms, Legs, Abs/Core, Full Body |
| completed | boolean, default false | |

### `meals`
| Column | Type | Notes |
| ------ | ---- | ----- |
| name | string | |
| description | text, nullable | |
| calories | integer, nullable | |
| meal_date | date, nullable | Defaults to today when saved |
| meal_time | string, nullable | Used for reminders |

### Health tracking
| Table | Columns | Constraint |
| ----- | ------- | ---------- |
| `bmi_histories` | date, height (float, m), weight (float, kg), bmi (float) | unique `(user_id, date)` |
| `step_histories` | date, steps (int, default 0) | unique `(user_id, date)` |
| `water_histories` | date, amount (int ml, default 0) | unique `(user_id, date)` |
| `daily_moods` | date, mood (`HAPPY`/`NEUTRAL`/`STRESSED`) | unique `(user_id, date)` |

### AI & notifications
| Table | Columns |
| ----- | ------- |
| `ai_chats` | session_id (uuid, nullable), user_message (text), ai_reply (longText) |
| `notifications` | message (string), read (bool, default false), scheduled_at (timestamp) |

### Framework tables
`password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.

---

## 8. API & Route Reference

Everything except the landing page and guest auth pages requires an authenticated session. JSON endpoints expect the `X-XSRF-TOKEN` / CSRF header, which axios sends automatically.

### Pages (Inertia)
| Method | URI | Name | Page |
| ------ | --- | ---- | ---- |
| GET | `/` | `home` | Welcome |
| GET | `/dashboard` | `dashboard` | Dashboard (`auth` + `verified`) |
| GET | `/workout` | `workout.index` | Workout |
| GET | `/meals` | `meals.index` | Meals/Index |
| GET | `/meals/create` | `meals.create` | Meals/Create |
| GET | `/meals/{meal}/edit` | `meals.edit` | Meals/Edit |
| GET | `/meal-assistant` | `meal.assistant` | MealAssistant |
| GET | `/notifications` | `notifications` | Notifications |
| GET | `/analytics/export` | — | AnalyticsExport |
| GET | `/profile` | `profile.edit` | Profile/Edit |

### Workouts
| Method | URI | Body / Validation | Response |
| ------ | --- | ----------------- | -------- |
| GET | `/workouts-data` | — | Array of the user's workouts (newest first) |
| POST | `/workouts-data` | `name` req·string·≤255, `sets` req·int·≥1, `reps` req·int·≥1, `date` req·date, `time` req, `calories` req·int·≥0, `completed` bool, `duration` int·≥0, `muscle_group` string·≤255 | `201` + workout |
| PUT | `/workouts-data/{workout}` | Same as POST | Updated workout |
| PUT | `/workouts-data/{workout}/complete` | — | Workout with `completed: true` |
| DELETE | `/workouts-data/{workout}` | — | `{ "message": "Deleted" }` |

### Meals (resource)
| Method | URI | Validation |
| ------ | --- | ---------- |
| POST | `/meals` | `name` req·≤255, `description` string, `calories` int, `meal_date` date, `meal_time` |
| PUT/PATCH | `/meals/{meal}` | Same as POST |
| DELETE | `/meals/{meal}` | — |

These routes redirect back to `/meals` with a flash message.

### Health tracking
| Method | URI | Body | Response example |
| ------ | --- | ---- | ---------------- |
| POST | `/bmi` | `height` 0.5–3, `weight` 10–500, `bmi` 5–100 | `{ "success": true, "bmi": {...} }`, or `422` `{ "error": "You can only update BMI once in a day" }` |
| GET | `/bmi-history` | — | `{ "history": [{ "date": "2026-01-05", "bmi": 22.4 }] }` |
| POST | `/steps` | `steps` int ≥ 1 | `{ "success": true, "steps": 8450 }` (today's total) |
| GET | `/steps/today` | — | `{ "steps": 8450 }` |
| GET | `/steps-history` | — | `{ "history": [{ "date": "...", "steps": 8450 }] }` |
| POST | `/water` | `amount` int ≥ 1 (ml) | `{ "success": true, "amount": 1500 }` (today's total) |
| GET | `/water/today` | — | `{ "amount": 1500 }` |
| GET | `/water-history` | — | `{ "history": [{ "date": "...", "amount": 1500 }] }` |
| POST | `/mood` | `mood` ∈ `HAPPY`,`NEUTRAL`,`STRESSED` | `{ "success": true, "mood": "HAPPY" }` |
| GET | `/mood/today` | — | `{ "mood": "HAPPY" }` or `{ "mood": null }` |
| GET | `/recent-activity` | — | `{ "workouts": [...] }` (today and tomorrow) |

### Dashboard settings
| Method | URI | Body | Response |
| ------ | --- | ---- | -------- |
| POST | `/calorie-goal` | `calorie_goal` int 500–10000 | `{ "success": true, "goal": 2500 }` |
| POST | `/user/calorie-goal` | `goal` int 1000–5000 | `{ "success": true }` (alternative endpoint) |

### AI coach
| Method | URI | Body | Response |
| ------ | --- | ---- | -------- |
| POST | `/ai/meal-assistant` | `prompt` req·string·≤1000, `session_id` string (optional) | `{ "reply": "…markdown…", "session_id": "uuid" }`, or `500` `{ "error": "AI service failed" }` |
| GET | `/ai/chat-history` | — | `[{ "session_id", "user_message", "created_at" }]`, one per session |
| GET | `/ai/chat/{session}` | — | All messages in the session, oldest first |
| DELETE | `/ai/chat/{session}` | — | `{ "success": true }` |

### Notifications
| Method | URI | Response |
| ------ | --- | -------- |
| GET | `/api/notifications` | Notifications with `scheduled_at <= now`, newest first |
| GET | `/api/notifications/unread-count` | Integer |
| POST | `/api/notifications/{notification}/read` | `{ "success": true }` |

### Export
| Method | URI | Query |
| ------ | --- | ----- |
| GET | `/analytics/export/csv` | `from=YYYY-MM-DD&to=YYYY-MM-DD` |
| GET | `/analytics/export/pdf` | `from=YYYY-MM-DD&to=YYYY-MM-DD` |

### Auth
| Method | URI | Notes |
| ------ | --- | ----- |
| GET/POST | `/register`, `/login` | Guest only |
| GET/POST | `/forgot-password`, `/reset-password/{token}`, `/reset-password` | Guest only |
| GET | `/auth/google` → `/auth/google/callback` | Google OAuth (guest only) |
| GET | `/verify-email`, `/verify-email/{id}/{hash}` | Signed, throttled |
| POST | `/email/verification-notification` | Throttled 6/min |
| GET/POST | `/confirm-password` | |
| PUT | `/password` | Change password |
| PATCH/DELETE | `/profile` | Update / delete account |
| POST | `/logout` | |

Run `php artisan route:list` to see every registered route.

---

## 9. External Services

| Service | Used for | Config | Required? |
| ------- | -------- | ------ | --------- |
| **Groq** | AI coach completions (`https://api.groq.com/openai/v1/chat/completions`) | `GROQ_API_KEY` → `config('services.groq.key')` | Only for the AI coach |
| **Google OAuth** | Sign in / sign up with Google | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_CLIENT_REDIRECT` | Only for Google login |
| **SMTP / mail provider** | Reminders, verification, password reset | `MAIL_*` | Optional; the default `log` mailer writes emails to `storage/logs/laravel.log` |
| **Open-Meteo** | Dashboard weather (called from the browser) | None | No key needed |
| **Neon / PostgreSQL** | Production database | `DB_*`, `DB_SSLMODE`, `DB_NEON_ENDPOINT` | Production |

---

## 10. Security

- **Authentication:** every app route sits behind the `auth` middleware, and the dashboard also requires a verified email.
- **Per-user data:** every query filters by `auth()->id()`.
- **Ownership checks:** updating or deleting a workout, meal or notification returns **403** if it belongs to another user.
- **CSRF:** Laravel's CSRF protection covers all state-changing requests, and axios sends the token automatically.
- **Passwords:** hashed with bcrypt (`BCRYPT_ROUNDS=12`); Google-only accounts have none.
- **XSS:** AI replies are parsed with `marked` and sanitised with **DOMPurify** before being inserted into the page.
- **Rate limiting:** login attempts and email verification are throttled (Breeze defaults).
- **HTTPS:** `URL::forceScheme('https')` in production.
- **Secrets:** API keys live only in `.env` and are never sent to the browser.

---

## 11. Getting Started

### Prerequisites

| Tool | Version | Check |
| ---- | ------- | ----- |
| PHP | 8.2+ with `pdo_sqlite` or `pdo_pgsql`, `mbstring`, `gd`, `dom`, `zip`, `bcmath` | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ (Docker uses 22) | `node -v` |
| npm | 9+ | `npm -v` |
| Git | any | `git --version` |

### Installation

```bash
# 1. Clone
git clone https://github.com/MAZubaer/Fitmate.git
cd Fitmate

# 2. PHP dependencies
composer install

# 3. JS dependencies
npm install            # add --legacy-peer-deps if you hit peer conflicts

# 4. Environment
cp .env.example .env   # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate

# 5. Database (SQLite default)
touch database/database.sqlite   # PowerShell: New-Item database/database.sqlite
php artisan migrate

# 6. Fill in GROQ_API_KEY, GOOGLE_* and MAIL_* in .env (see Configuration)
```

> **One-liner:** `composer run setup` installs dependencies, creates `.env`, generates the key, migrates and builds the assets.

### Run in development

```bash
composer run dev
```

This starts four processes with `concurrently`:

| Name | Command | Purpose |
| ---- | ------- | ------- |
| server | `php artisan serve` | App on http://localhost:8000 |
| queue | `php artisan queue:listen --tries=1` | Queue worker |
| logs | `php artisan pail` | Live log tail |
| vite | `npm run dev` | Hot module reloading |

You can also run `php artisan serve` and `npm run dev` in two separate terminals. Add `php artisan schedule:work` if you want reminders.

### Build for production

```bash
npm run build
php artisan config:cache route:cache view:cache
```

---

## 12. Configuration (Environment Variables)

`.env.example` contains Laravel's defaults plus empty `GROQ_API_KEY` and `GOOGLE_*` entries. Fill them in, and adjust the rest as needed:

```env
APP_NAME=FitMate
APP_ENV=local
APP_URL=http://localhost:8000

# ---------- Database ----------
DB_CONNECTION=sqlite
# PostgreSQL / Neon:
# DB_CONNECTION=pgsql
# DB_HOST=ep-xxxx.region.aws.neon.tech
# DB_PORT=5432
# DB_DATABASE=fitmate
# DB_USERNAME=your-user
# DB_PASSWORD=your-password
# DB_SSLMODE=require
# DB_NEON_ENDPOINT=ep-xxxx        # Neon endpoint ID, for clients without SNI support

# ---------- Sessions / cache / queue ----------
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# ---------- AI coach ----------
GROQ_API_KEY=gsk_xxxxxxxxxxxxxxxxx

# ---------- Google OAuth ----------
GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxxxxx
GOOGLE_CLIENT_REDIRECT=http://localhost:8000/auth/google/callback

# ---------- Mail ----------
MAIL_MAILER=smtp                  # use "log" for local testing
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@example.com
MAIL_PASSWORD=app-password
MAIL_FROM_ADDRESS="no-reply@fitmate.app"
MAIL_FROM_NAME="${APP_NAME}"
```

| Variable | Description |
| -------- | ----------- |
| `APP_URL` | Base URL; must be `https://` in production |
| `DB_*` | Database connection |
| `DB_NEON_ENDPOINT` | Adds `options='endpoint=…'` to the Postgres host for Neon |
| `GROQ_API_KEY` | Groq API key from [console.groq.com](https://console.groq.com) |
| `GOOGLE_CLIENT_*` | OAuth client from Google Cloud Console (see [OAUTH_SETUP.md](OAUTH_SETUP.md)) |
| `MAIL_*` | Outgoing mail for reminders and auth emails |

---

## 13. Running the Scheduler

`routes/console.php` registers:

```php
Schedule::command('reminders:send')->everyMinute();
```

**What `reminders:send` does** (`app/Console/Commands/SendReminderEmails.php`):

1. Takes the current time and a window starting 2 minutes earlier
2. For each **meal**, combines `meal_date + meal_time`; for each **workout**, `date + time`
3. If that time falls inside the window and no reminder notification with the same message exists yet:
   - sends an email: **"🍽 FitMate Meal Reminder"** / **"💪 FitMate Workout Reminder"**
   - creates an in-app notification (`Meal Reminder: …` / `Workout Reminder: …`)

**Local:**
```bash
php artisan schedule:work          # runs the scheduler continuously
php artisan reminders:send         # run the check once
```

**Server (cron):**
```cron
* * * * * cd /path/to/fitmate && php artisan schedule:run >> /dev/null 2>&1
```

On Render, create a **Cron Job** service that runs `php artisan schedule:run` every minute with the same environment variables.

---

## 14. Testing & Code Style

The test suite uses **PHPUnit 11** and includes the Breeze feature tests:

- `tests/Feature/Auth/*`: authentication, registration, email verification, password confirmation, reset and update
- `tests/Feature/ProfileTest.php`: profile update and account deletion
- `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`

```bash
composer test          # clears config cache, then runs php artisan test
php artisan test --filter=ProfileTest
```

`phpunit.xml` configures the testing environment.

**Formatting:**
```bash
./vendor/bin/pint          # Laravel Pint (PSR-12 based)
```

The `.editorconfig` file sets indentation and line endings.

---

## 15. Deployment

FitMate is deployed as a **Docker** web service on **[Render](https://render.com)** with a **PostgreSQL** database (e.g. **[Neon](https://neon.tech)**).

### Dockerfile stages

| Stage | Base | Does |
| ----- | ---- | ---- |
| `php-base` | `php:8.2-cli-alpine` | Installs `bcmath`, `dom`, `exif`, `gd` (freetype/jpeg/webp), `mbstring`, `pcntl`, `pdo_pgsql`, `zip` |
| `vendor` | `php-base` + Composer 2 | `composer install --no-dev --optimize-autoloader` |
| `frontend` | `node:22-alpine` | `npm ci && npm run build` (copies `vendor` so Ziggy resolves) |
| `runtime` | `php-base` | Copies the app and `public/build`, prepares `storage/` permissions, sets `APP_ENV=production` and `APP_DEBUG=false`, runs `php artisan serve --host=0.0.0.0 --port=$PORT` |

### Render settings

| Setting | Value |
| ------- | ----- |
| Service type | Web Service, Docker runtime |
| Pre-Deploy Command | `php artisan migrate --force` |
| Health check path | `/` |
| Environment | `APP_KEY`, `APP_URL=https://…`, `DB_CONNECTION=pgsql`, `DB_*`, `DB_SSLMODE=require`, `GROQ_API_KEY`, `GOOGLE_*`, `MAIL_*`, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` |
| Cron Job (separate service) | `php artisan schedule:run` every minute |

### Production notes
- **HTTPS:** `AppServiceProvider` forces `https` URLs when `APP_ENV=production`, so assets, redirects and signed URLs work behind Render's TLS proxy.
- **Postgres + poolers:** the `pgsql` connection sets `PDO::ATTR_EMULATE_PREPARES => true` to avoid "cached plan must not change result type" errors with Neon / PgBouncer after schema changes.
- **Boolean queries:** the dashboard filters completed workouts with `completed = TRUE`, which PostgreSQL requires.
- **Google OAuth:** set `GOOGLE_CLIENT_REDIRECT=https://<your-domain>/auth/google/callback` and add the same URI in Google Cloud Console.

### Run the production image locally

```bash
docker build -t fitmate .
docker run --rm -p 8000:8000 --env-file .env fitmate
# run migrations once:
docker run --rm --env-file .env fitmate php artisan migrate --force
```

---

## 16. Troubleshooting

| Problem | Fix |
| ------- | --- |
| `Vite manifest not found` | Run `npm run dev` (development) or `npm run build` |
| Blank page / old assets | Hard refresh; check that `npm run dev` is running; `php artisan optimize:clear` |
| `database/database.sqlite does not exist` | `touch database/database.sqlite`, then `php artisan migrate` |
| AI coach returns "AI service failed" | Check `GROQ_API_KEY`, run `php artisan config:clear` and check `storage/logs/laravel.log` |
| Google login redirects back with an error | Make sure `GOOGLE_CLIENT_REDIRECT` exactly matches an authorised redirect URI in Google Cloud Console |
| No reminder emails | Run `php artisan schedule:work` and set real `MAIL_*` values (with `MAIL_MAILER=log`, emails go to the log) |
| BMI: "You can only update BMI once a day" | This is expected; one BMI entry per day |
| 419 Page Expired | The session or CSRF token expired; refresh the page. Check that `SESSION_DRIVER=database` and that migrations ran. |
| Postgres "cached plan must not change result type" | Already handled by emulated prepares; redeploy or restart the service after migrations |
| npm peer-dependency errors | `npm install --legacy-peer-deps` |
| Mixed content / http links in production | Set `APP_ENV=production` and `APP_URL=https://…` |

---

## 17. Known Limitations

These are current behaviours worth knowing about, and good first contributions:

- **Weather location is fixed** to Dhaka, and the app time zone is `Asia/Dhaka` for all users.
- **Duplicate reminders:** a scheduled meal or workout creates a notification when it's saved *and* another when `reminders:send` runs, so users can see two notifications for one item.
- **Reminder performance:** `reminders:send` loads every meal and workout each minute and sends mail synchronously, which will be slow with many records.
- **Two calorie-goal endpoints** with different limits exist (`/calorie-goal`: 500–10,000; `/user/calorie-goal`: 1,000–5,000). The dashboard uses the first.
- The dashboard's `total_workouts` stat is currently a placeholder (always `0`).
- The workout page marks a workout complete with `PUT /workouts-data/{id}` rather than the dedicated `/complete` endpoint.
- The profile dropdown links to `/settings`, which has no route yet.
- `Route::resource('meals', …)` is registered twice in `routes/web.php`. This is harmless.
- Tests cover only authentication and profile; there are no feature tests for workouts, meals, health tracking or the AI coach yet.

---

## 18. Contributing

1. Read the team guides:
   - [CONTRIBUTING.md](CONTRIBUTING.md): environment setup and contribution flow
   - [GIT_WORKFLOW.md](GIT_WORKFLOW.md): branching and commit conventions
   - [TEAM_SETUP.md](TEAM_SETUP.md) and [TEAM_COLLABORATION.md](TEAM_COLLABORATION.md): onboarding
   - [QUICK_REFERENCE.md](QUICK_REFERENCE.md): Git cheat sheet
   - [OAUTH_SETUP.md](OAUTH_SETUP.md): Google OAuth setup
2. Create a branch: `git checkout -b feature/short-description`
3. Make your change. Follow the existing style and run `./vendor/bin/pint`.
4. Run `php artisan test`
5. Commit with a clear message and push: `git push origin feature/short-description`
6. Open a Pull Request against `main`

See the [contributors page](https://github.com/MAZubaer/Fitmate/graphs/contributors) for everyone who has worked on FitMate.

---

## 19. License

FitMate is open-source software released under the [MIT License](LICENSE).
