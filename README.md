# LabGato — Professional Laboratory Toolkit

A Laravel platform for laboratory professionals: clinical calculators, reference ranges,
SOPs, interpretation guides, and a knowledge hub — with community contributions,
RBAC admin, AI drafting, and analytics.

## Stack
- Laravel 11 · PHP 8.2+ · MySQL
- Tailwind CSS (Abyss Blue theme, dark mode) · Alpine.js · Livewire
- Laravel Socialite (Google OAuth) · Groq AI

## Local Setup
1. `composer install`
2. `npm install && npm run build`
3. `cp .env.example .env` then `php artisan key:generate`
4. Configure MySQL in `.env`, then `php artisan migrate --seed`
5. `php artisan serve`

## Default Seeds
- Settings, Roles/Permissions, Reference Range data, and a Super Admin are seeded.

> See `.env.example` for AI (Groq) and Google OAuth configuration.
