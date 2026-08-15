# Step 1: Build Process

Prepare production assets on your local machine before uploading to Hostinger.

## Prerequisites

- PHP 8.4 or higher
- Composer
- Node.js and npm

## Build Commands

```bash
# Install PHP dependencies (no dev, optimized)
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm ci

# Build frontend assets
npm run build
```

## Verify Build

1. Check that `public/build/` directory exists
2. Verify CSS and JS files are present in `public/build/assets/`
3. Check that `public/build/manifest.json` exists

## Create Production Archive

```bash
# Create a tar.gz archive (excluding development files)
tar -czf Labgoto-production.tar.gz \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='tests' \
  --exclude='deploy' \
  --exclude='docs' \
  --exclude='*.md' \
  --exclude='.gitignore' \
  --exclude='.editorconfig' \
  --exclude='phpunit.xml' \
  --exclude='tailwind.config.js' \
  --exclude='vite.config.js' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  .
```
