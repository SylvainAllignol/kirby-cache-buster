# Kirby Cache Buster

A simple and lightweight Kirby plugin to handle **cache busting for CSS and JS files**

## Installation

### Via Composer (recommended)

```bash
composer require allignol/kirby-cache-buster
```

Add the following rules to the .htaccess file at the root of your site:

```bash
# --------------------------------------------------------
# Cache busting for CSS / JS
# --------------------------------------------------------

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)\.([a-zA-Z0-9]+)\.(js|css)$ $1.$3 [L]
```