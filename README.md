# Labgoto — Professional Laboratory Toolkit

A Laravel platform for laboratory professionals: clinical calculators, reference ranges,
SOPs, interpretation guides, and a knowledge hub — with community contributions,
RBAC admin, AI drafting, and analytics.

## Stack
- Laravel 13 · PHP 8.4 · MySQL
- Tailwind CSS 4 (Abyss Blue theme, dark mode) · Alpine.js · Livewire 4
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

## Copyright

© 2026 Samuel Ogundare. All rights reserved.

This source is published for portfolio and reference purposes only. No permission
is granted to copy, modify, redistribute, or deploy it, in whole or in part.
Licensing enquiries: [admin@smevolve.tech](mailto:admin@smevolve.tech).
