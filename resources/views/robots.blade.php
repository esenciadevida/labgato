# LabGoTo robots.txt
# Public pages are fully crawlable; private and utility areas are blocked.

User-agent: *
Allow: /

# Private and authenticated areas
Disallow: /admin
Disallow: /auth
Disallow: /account

# JSON API
Disallow: /api

# Search result pages (avoid crawl bloat)
Disallow: /search

Sitemap: {{ config('app.url') }}/sitemap.xml
