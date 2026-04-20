# Volt Gym WordPress-Compatible Theme

This is a standalone PHP theme designed for Volt Gym and connected to the FitApp Laravel API backend. It can be deployed on any PHP server and is prepared for future WordPress integration.

## Features

- Session-based authentication
- API communication with Laravel backend
- Role-based access (client, staff, admin)
- Responsive design with Tailwind CSS
- WordPress-style functions and template structure

## Setup

1. Ensure the Laravel API is running on `http://127.0.0.1:8000/api/v1` (or update `API_BASE` in `includes/functions.php`)
2. Deploy the theme files to your PHP server
3. Access `index.php` to start

## Structure

- `client/` - Client-facing pages
- `staff/` - Staff/admin pages
- `shared/` - Common pages (login, register, etc.)
- `includes/` - Core functions
- `template-parts/` - Reusable components

## WordPress Integration

When moving to WordPress:
- The wrapper files in the root will be detected by WP
- Shortcodes can be added in `functions.php`
- Cache functions are available for transients

## Cache

Transient cache is implemented using file storage in `cache/` directory.

## Security

- Input sanitization with `h()` and `esc_url()`
- Role-based access controls
- Security headers in `.htaccess`