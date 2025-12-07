# Google OAuth Setup Guide

## Step 1: Get Google OAuth Credentials

1. **Go to [Google Cloud Console](https://console.cloud.google.com/)**
   - Sign in with your Google account
   - Create a new project (or select existing)

2. **Create OAuth 2.0 Credentials**
   - Go to "Credentials" in the left sidebar
   - Click "Create Credentials" → "OAuth client ID"
   - Choose "Web application"
   - Name it "FitMate" (or your app name)

3. **Add Authorized Redirect URIs**
   - Add: `http://localhost:8000/auth/google/callback`
   - For production, add your live domain: `https://yourdomain.com/auth/google/callback`
   - Click "Create"

4. **Copy Credentials**
   - Copy the "Client ID" and "Client Secret"
   - Keep these safe and private!

## Step 2: Configure Environment Variables

Edit `.env` file and add your Google credentials:

```env
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_CLIENT_REDIRECT=http://localhost:8000/auth/google/callback
```

For production:
```env
GOOGLE_CLIENT_REDIRECT=https://yourdomain.com/auth/google/callback
```

## Step 3: Update Login Page UI

Add the Google login button to `resources/js/Pages/Auth/Login.vue`:

```vue
<script setup>
import { Link } from '@inertiajs/vue3';

// ... existing imports
</script>

<template>
    <!-- Existing form -->
    <form @submit.prevent="form.post(route('login'))">
        <!-- ... existing form fields ... -->
    </form>

    <!-- Add this Google OAuth button -->
    <div class="mt-6">
        <a
            :href="route('auth.google')"
            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <!-- Google logo SVG -->
            </svg>
            Sign in with Google
        </a>
    </div>
</template>
```

## Step 4: Update Register Page UI

Add the same button to `resources/js/Pages/Auth/Register.vue` for consistency.

## File Changes Made

✅ **Created/Modified:**
- `app/Http/Controllers/Auth/OAuthController.php` - OAuth handler
- `database/migrations/2025_12_07_000000_add_oauth_to_users_table.php` - Database schema
- `app/Models/User.php` - Updated fillable array
- `routes/auth.php` - Added OAuth routes
- `config/services.php` - Added Google config
- `.env` - Added Google credentials

## How It Works

1. User clicks "Sign in with Google" button
2. Redirected to: `route('auth.google')`
3. User authenticates with Google
4. Google redirects back to: `/auth/google/callback`
5. OAuthController handles the callback:
   - Finds or creates user based on Google ID
   - Creates user if new (auto-verifies email)
   - Updates tokens if returning user
   - Logs in the user
   - Redirects to dashboard

## Database Fields Added

```sql
- google_id (string, unique)
- google_token (string, nullable)
- google_refresh_token (string, nullable)
```

## Testing

1. Start your dev server:
   ```bash
   php artisan serve
   ```

2. Visit `http://localhost:8000/login`

3. Click "Sign in with Google"

4. Authenticate with your Google account

5. You should be redirected to dashboard after successful auth

## Troubleshooting

### "Invalid Client ID" error
- Check that `GOOGLE_CLIENT_ID` in `.env` is correct
- Verify OAuth credentials in Google Cloud Console

### "Redirect URI mismatch" error
- Ensure the callback URL in Google Cloud Console matches exactly:
  - `http://localhost:8000/auth/google/callback` (for local dev)
- No trailing slash, exact match required

### User not logging in
- Check that migrations ran: `php artisan migrate`
- Verify database connection in `.env`
- Check logs: `storage/logs/laravel.log`

### Missing email from Google
- Some Google accounts don't provide email
- Add fallback in OAuthController if needed

## Security Notes

⚠️ **Never commit `.env` with real credentials!**
- Always use `.gitignore` to exclude `.env`
- Use different credentials for dev/staging/production
- Rotate credentials regularly
- Keep `GOOGLE_CLIENT_SECRET` secret!

## Next Steps

1. Customize the user model if needed
2. Add avatar storage from Google profile
3. Add more OAuth providers (GitHub, Facebook, etc.)
4. Implement token refresh for offline access
5. Add user preferences for OAuth linking

## Useful Resources

- [Socialite Documentation](https://laravel.com/docs/socialite)
- [Google OAuth Setup](https://developers.google.com/identity/protocols/oauth2)
- [Socialite Providers](https://socialiteproviders.com)
