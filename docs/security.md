# Security Hardening

This document captures the operational hardening guidance for LabGato deployments
(DB least-privilege and repository hygiene). Code-level controls are implemented
in this build and verified by `tests/Feature/Security/*`.

## Database least-privilege

Create a dedicated MySQL user for the application with only the privileges it needs.
**No `DROP`, `ALTER`, or `CREATE` in production** — schema changes should be applied
with a separate migration-time user (or CI account).

```sql
CREATE USER 'labgato_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON labgato.* TO 'labgato_app'@'localhost';
FLUSH PRIVILEGES;
```

Update `.env`:

```
DB_USERNAME=labgato_app
DB_PASSWORD=strong_password
```

## Git hygiene

- `.env` and per-environment secrets are already git-ignored (`.env`, `.env.*`,
  `storage/*.key`, `auth.json`). `.env.example` remains tracked as a template.
- If secrets were ever committed:
  1. Rotate the exposed secrets immediately (new APP_KEY, DB password, API keys).
  2. Rewrite history to purge them: `git filter-repo --invert-paths --path .env`
  3. Force-push the rewritten history and coordinate with anyone who cloned the repo.
- Never commit `storage/` contents, `vendor/`, or `node_modules/`.

## Deployed app checklist

- `APP_DEBUG=false`
- `APP_ENV=production`
- `SESSION_SECURE_COOKIE=true`
- HTTPS terminated at the proxy; HSTS is set by both the app middleware and `.htaccess`.
- Request body limited to 1 MB for non-upload requests (upload routes opt out).
