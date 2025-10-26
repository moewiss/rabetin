# First-Time User Wizard Setup Guide

This guide explains the new first-time user wizard feature for Rabetin.bio.

## What's New?

A beautiful, multi-step onboarding wizard now appears automatically for new users immediately after they sign up and log in for the first time. This wizard guides users through:

1. **Basic Info**: Setting up their display name and bio
2. **Customize Look**: Uploading an avatar and choosing a theme (dark/light)
3. **First Link**: Adding their first custom link
4. **Completion**: A welcome screen with tips on what to do next

## Files Added/Modified

### New Files Created:
- `wizard.php` - The main wizard interface with 4-step onboarding flow
- `save_wizard.php` - Backend script to process wizard form submission
- `database_migration_wizard.sql` - SQL migration to add wizard tracking

### Modified Files:
- `login.php` - Now checks if user completed wizard and redirects accordingly
- `includes/auth.php` - Added `require_wizard_completed()` helper function
- `dashboard/index.php` - Added wizard completion check and welcome message

## Installation Steps

### Step 1: Run the Database Migration

Run the following SQL command to add the `wizard_completed` column to your users table:

```bash
mysql -u rabetin -p rabetin_bio < database_migration_wizard.sql
```

Or manually run this SQL:

```sql
ALTER TABLE users ADD COLUMN wizard_completed TINYINT(1) DEFAULT 0;
```

### Step 2: (Optional) Mark Existing Users as Completed

If you have existing users and want them to skip the wizard, run:

```sql
UPDATE users SET wizard_completed = 1;
```

This way, only new signups will see the wizard. Existing users will continue directly to the dashboard.

### Step 3: Test the Feature

1. Create a new test account at `/signup.php`
2. Log in with the new account
3. You should be automatically redirected to `/wizard.php`
4. Complete the wizard steps
5. You'll be redirected to the dashboard with a welcome message

## How It Works

### User Flow:

1. **New User Signs Up** → `signup.php` creates user with `wizard_completed = 0`
2. **User Logs In** → `login.php` checks `wizard_completed` flag
3. **First Login** → Redirects to `/wizard.php` (wizard_completed = 0)
4. **Subsequent Logins** → Redirects to `/dashboard/index.php` (wizard_completed = 1)

### Technical Details:

- **Database Tracking**: The `wizard_completed` column in the `users` table tracks whether a user has completed the wizard
- **Authentication Guard**: The `require_wizard_completed()` function ensures users complete the wizard before accessing the dashboard
- **One-Time Experience**: Once completed, users never see the wizard again
- **Skip Options**: Users can skip the optional steps (like adding a link)

## Features

### Beautiful UI
- Modern gradient design with purple theme
- Smooth animations and transitions
- Progress bar showing completion status
- Step-by-step navigation

### User-Friendly
- Clear instructions at each step
- Preview for uploaded avatars
- Skip options for non-essential steps
- Success screen with helpful next steps

### Comprehensive Setup
- Profile basics (name, bio)
- Avatar upload
- Theme selection (dark/light)
- First custom link
- Welcome tips

## Customization

### Changing Wizard Steps

Edit `wizard.php` to modify the steps. Each step is a `.step` div with `data-step` attribute:

```html
<div class="step" data-step="1">
  <!-- Your custom step content -->
</div>
```

Update `totalSteps` in the JavaScript section if you add/remove steps:

```javascript
const totalSteps = 4; // Update this number
```

### Styling

All styles are embedded in `wizard.php`. Key style classes:

- `.wizard-container` - Main container
- `.wizard-header` - Purple gradient header
- `.step` - Individual step container
- `.theme-option` - Theme selector cards
- `.btn-primary` / `.btn-secondary` - Button styles

### Force Show Wizard

To force the wizard to appear for a specific user (for testing):

```sql
UPDATE users SET wizard_completed = 0 WHERE username = 'testuser';
```

## Troubleshooting

### Wizard doesn't appear for new users
- Check if the database column was added successfully: `DESCRIBE users;`
- Verify the user's `wizard_completed` value: `SELECT wizard_completed FROM users WHERE username = 'username';`

### Existing users see the wizard
- Run the SQL to mark existing users as completed (see Step 2 above)

### File upload errors
- Check that `uploads/profile_images/` directory exists and is writable
- Verify PHP file upload settings in `php.ini`

### Redirect loop
- Clear browser cookies/cache
- Verify database connection is working
- Check that `wizard_completed` column was added successfully

## Support

For questions or issues, check:
- Database connection in `includes/db.php`
- PHP error logs for detailed error messages
- Browser console for JavaScript errors

## Future Enhancements

Potential improvements you could add:

- Add more steps (social accounts, background customization)
- Save progress (allow users to complete wizard later)
- Add wizard "replay" button in dashboard for existing users
- Collect analytics on wizard completion rates
- Add video tutorials or GIFs to guide users

---

**Version**: 1.0  
**Last Updated**: October 2025

