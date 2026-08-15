# Step 2: Upload

Upload the production build to Hostinger shared hosting.

## Upload Methods

### Method 1: File Manager (Recommended)

1. Log in to Hostinger control panel
2. Open File Manager
3. Navigate to `public_html/` or your domain directory
4. Upload the production archive
5. Extract the archive in the File Manager

### Method 2: SFTP

```bash
# Connect via SFTP
sftp your_username@your_host

# Navigate to public_html
cd public_html

# Upload archive
put Labgoto-production.tar.gz

# Extract on server
# (use SSH if available, or File Manager)
```

### Method 3: Git (if SSH access available)

```bash
# Clone repository on server
cd public_html
git clone https://github.com/your-repo/Labgoto.git .

# Checkout production branch
git checkout main
```

## Configure Document Root

Ensure your domain's document root points to the `public/` directory, not the project root.

- Hostinger Panel → Domains → Your Domain → Advanced → Document Root
- Set to: `public_html/public/` or `/public_html/public`

## Set File Permissions

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make storage and bootstrap writable
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Ensure .htaccess is readable
chmod 644 public/.htaccess
```
