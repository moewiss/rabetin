# Rabetin.bio - Bio Link Builder

A simple, lightweight bio link builder application (like Linktree) built with pure PHP.

## Features

- 👤 **Profile Customization** - Display name, bio, avatar, theme (dark/light), and custom fonts
- 🎨 **Background Options** - Solid colors, gradients, or custom images
- 🔗 **Social Media Icons** - Connect all your social accounts (Instagram, Facebook, X, TikTok, YouTube, LinkedIn, GitHub, etc.)
- 📱 **Custom Links** - Add unlimited custom links with drag-and-drop reordering
- 📺 **Embeds** - Display Instagram posts, TikTok videos, or any custom iframe content
- 🎯 **Guided Tour** - Interactive onboarding tour for new users
- 🔐 **User Authentication** - Secure login and signup system
- 🔄 **Odoo Integration** - Optional integration with Odoo for subscription management

## Requirements

- PHP 8.2 or higher
- MySQL or MariaDB database
- Composer
- Node.js and npm (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/rabetin.bio.git
cd rabetin.bio
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install frontend dependencies:
```bash
npm install
```

4. Create a database and import the schema (create your database schema based on the tables used in the code)

5. Configure your database connection in `includes/db.php`

6. (Optional) If using Odoo integration, set up environment variables:
   - `ODOO_URL` - Your Odoo instance URL
   - `ODOO_DB` - Database name
   - `ODOO_EMAIL` - Odoo username/email
   - `ODOO_API_KEY` - Odoo API key

7. Build frontend assets:
```bash
npm run build
```

8. Set up your web server to point to the project root directory

## Usage

### For Users

1. Sign up for an account at `/signup.php`
2. Log in at `/login.php`
3. Access your dashboard at `/dashboard/`
4. Follow the interactive tour to set up your profile
5. Share your bio link: `yourdomain.com/yourusername`

### For Developers

#### Project Structure

```
rabetin.bio/
├── dashboard/          # Dashboard pages
│   ├── index.php      # Main dashboard
│   ├── save_*.php     # Form handlers
│   └── ...
├── includes/          # Core PHP includes
│   ├── db.php         # Database connection
│   ├── auth.php       # Authentication helpers
│   └── services/      # Service classes
│       └── OdooService.php  # Odoo integration
├── assets/            # CSS and JS source files
├── public/            # Public assets
│   └── build/         # Built frontend assets
├── uploads/           # User uploaded files
│   └── profile_images/
├── index.php          # Landing page router
├── profile.php        # User profile display
├── login.php          # Login page
├── signup.php         # Registration page
└── logout.php         # Logout handler
```

#### Database Tables

The application uses the following tables:
- `users` - User accounts
- `profiles` - User profile settings
- `links` - Custom links
- `social_accounts` - Social media accounts
- `embeds` - Embedded content

## Odoo Integration

The OdooService class provides integration with Odoo ERP for subscription management:

```php
require __DIR__.'/includes/services/OdooService.php';

$odoo = new OdooService();
$plans = $odoo->getPlans();
$partnerId = $odoo->ensurePartner(['email' => 'user@example.com', 'name' => 'John Doe']);
```

## Development

To run the development server with live reloading:

```bash
npm run dev
```

To build production assets:

```bash
npm run build
```

## Technologies Used

- **Backend**: PHP 8.2+
- **Frontend**: TailwindCSS, Alpine.js, Vite
- **Libraries**: 
  - GuzzleHTTP (for Odoo API calls)
  - SortableJS (drag-and-drop)
  - Driver.js (guided tours)

## License

MIT License

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
