# Rebuild frontend assets and sync them to the production server.
# Run from the repo root. Requires: npm, tar, scp, and the SSH host "hostinger-labgoto".
#
# Use this after changing any Blade markup that introduces a NEW Tailwind class,
# or any change to resources/css / resources/js. Without it, new utility classes
# silently have no effect in production. It also recompiles Blade views.
$ErrorActionPreference = 'Stop'

$remote     = 'hostinger-labgoto'
$serverApp  = '/home/u226366554/domains/labgoto.com/public_html'
$serverPub  = "$serverApp/public"
$archive    = Join-Path $env:TEMP 'labgoto-build.tar.gz'

Write-Host '==> Building frontend assets...' -ForegroundColor Cyan
npm run build

Write-Host '==> Packaging public/build...' -ForegroundColor Cyan
tar -czf $archive -C public build

Write-Host '==> Uploading + extracting on server...' -ForegroundColor Cyan
scp $archive "${remote}:~/labgoto-build.tar.gz"
ssh $remote "cd '$serverPub' && rm -rf build && tar -xzf ~/labgoto-build.tar.gz && rm -f ~/labgoto-build.tar.gz"

Write-Host '==> Recompiling views (optimize)...' -ForegroundColor Cyan
ssh $remote "cd '$serverApp' && /opt/alt/php84/usr/bin/php artisan optimize"

Remove-Item $archive -ErrorAction SilentlyContinue
Write-Host '==> Done.' -ForegroundColor Green
