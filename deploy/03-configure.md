# Step 3: Configure

Set up the production environment configuration.

## Copy Environment File

```bash
# Copy the production template
cp .env.production.example .env
```

## Edit .env File

Open `.env` in a text editor and set the following values:

### Application Settings

```
APP_NAME="Labgoto"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
# Required when nginx (or another proxy) terminates TLS. Needed for the
# HTTPS redirect and HSTS header to work. Leave unset only if TLS terminates
# on the PHP server itself.
TRUSTED_PROXIES=*
```

### Database Settings

```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### Session Settings

```
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### Mail Settings (if needed)

```
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

## Generate APP_KEY

```bash
# Via SSH
php artisan key:generate

# If SSH is not available, generate a key locally and paste it
# The key should be 32 characters (base64 encoded)
APP_KEY=base64:your_generated_key_here
```

## Verify Configuration

```bash
# Check configuration
php artisan config:cache
php artisan config:clear
```
