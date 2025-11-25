# Quick Fix: Railway File Upload Size Limit

## The Problem
Your phpinfo shows: **Loaded Configuration File: (none)** - Railway's Nix-based PHP doesn't read standard php.ini files.

## ⚠️ CRITICAL: Cannot Set Per-Function
`upload_max_filesize` and `post_max_size` are **PHP_INI_SYSTEM** directives. They **CANNOT** be set:
- ❌ In a controller function
- ❌ With `ini_set()` at runtime
- ❌ Per-route or per-middleware

They **MUST** be set **before PHP starts** via:
- ✅ Railway Environment Variables (RECOMMENDED)
- ✅ `.user.ini` file (fallback)

## Solution 1: Railway Environment Variables (BEST)

1. Go to https://railway.app → Your Project → Variables tab
2. Add these environment variables:

```
PHP_INI_UPLOAD_MAX_FILESIZE=100M
PHP_INI_POST_MAX_SIZE=100M
PHP_INI_MEMORY_LIMIT=512M
PHP_INI_MAX_EXECUTION_TIME=300
```

3. **Redeploy** (Railway auto-redeploys when you add variables)
4. Verify at: `https://your-app.up.railway.app/phpinfo`
   - Check "Core" section
   - `upload_max_filesize` should show `100M`
   - `post_max_size` should show `100M`

## Solution 2: .user.ini File (Fallback)

A `.user.ini` file has been created in `public/.user.ini`. Some PHP configurations read this automatically.

**Note**: Railway's Nix-based PHP might not read `.user.ini`, so Solution 1 is preferred.

## Solution 3: Nginx Configuration (If Railway uses Nginx)

If Railway uses Nginx as a reverse proxy, you may also need to set `client_max_body_size`. An `nginx.conf` file has been created in the project root.

## Verification

After deploying, check your phpinfo page. The values should match:
- `upload_max_filesize = 100M`
- `post_max_size = 100M`

If they don't match, Railway might not be reading the configuration. Check Railway's documentation or support for PHP configuration options.

