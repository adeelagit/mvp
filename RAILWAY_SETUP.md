# Railway PHP Configuration for Large File Uploads

## Problem
Railway uses a Nix-based PHP installation that doesn't read `php.ini` from the project root. The `upload_max_filesize` and `post_max_size` directives are **PHP_INI_SYSTEM level**, meaning they **CANNOT be changed with `ini_set()`** at runtime and must be set before PHP starts.

**⚠️ IMPORTANT: You CANNOT set these per-function or per-controller. They are system-level settings.**

## Solution: Set Railway Environment Variables

Since Railway uses Nix-based PHP, you need to configure these settings via **Railway Environment Variables**.

### Steps:

1. **Go to your Railway project dashboard**: https://railway.app
2. **Navigate to your service/project**
3. **Click on "Variables" tab** (or "Environment" tab)
4. **Add the following environment variables:**

```
PHP_INI_UPLOAD_MAX_FILESIZE=100M
PHP_INI_POST_MAX_SIZE=100M
PHP_INI_MEMORY_LIMIT=512M
PHP_INI_MAX_EXECUTION_TIME=300
PHP_INI_MAX_INPUT_TIME=300
```

5. **Redeploy your application** (Railway will automatically redeploy when you add variables, or you can manually trigger a redeploy)

### Alternative Methods (if PHP_INI_* variables don't work):

1. **Try `.user.ini` file**: A `.user.ini` file has been created in `public/.user.ini` - some PHP configurations read this
2. **Check Railway's buildpack**: Railway might use a specific PHP buildpack that supports different variable names
3. **Custom buildpack**: You may need to use a custom buildpack that supports php.ini configuration

### Verify Configuration

After setting the environment variables:
1. **Redeploy your application** on Railway
2. **Visit** `https://your-app.up.railway.app/phpinfo`
3. **Check** the "Core" section for:
   - `upload_max_filesize` should show `100M`
   - `post_max_size` should show `100M`

### Current Status

Based on your phpinfo output:
- **Loaded Configuration File**: `(none)` - Railway is not loading php.ini
- **Configuration File Path**: `/nix/store/...` - Nix-based PHP installation
- You need to use Railway's environment variable system

### Note

The code in `ServiceTicketController::store()` now includes `ini_set()` calls as a fallback, but these will NOT work for `upload_max_filesize` and `post_max_size` on Railway. The environment variables are the only reliable solution.

