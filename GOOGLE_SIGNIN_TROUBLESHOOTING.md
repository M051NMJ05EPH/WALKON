# Google Sign-In Troubleshooting Guide

## 🔍 Quick Diagnostic

**Run the diagnostic script first:**
```
http://localhost/MINIPROJECT2.0/test-google-oauth.php
```

This will automatically check all system requirements and show you exactly what's wrong.

---

## Common Issues & Solutions

### 1. ❌ "redirect_uri_mismatch" Error

**Cause:** The redirect URI in your code doesn't match Google Cloud Console

**Solution:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Navigate to: **APIs & Services** → **Credentials**
3. Click on your OAuth 2.0 Client ID
4. Under **Authorized redirect URIs**, add:
   ```
   http://localhost/MINIPROJECT2.0/google-callback.php
   ```
5. Click **Save**
6. Wait 5 minutes for changes to propagate

---

### 2. ❌ "Failed to get access token from Google"

**Possible Causes:**
- cURL is not enabled in PHP
- Invalid Client ID or Client Secret
- Network/firewall blocking Google APIs

**Solutions:**

#### Enable cURL:
1. Open `php.ini` (usually in `C:\xampp\php\php.ini`)
2. Find this line: `;extension=curl`
3. Remove the semicolon: `extension=curl`
4. Restart Apache in XAMPP
5. Verify by running `test-google-oauth.php`

#### Verify Credentials:
1. Check `google-config.php` has correct:
   - `GOOGLE_CLIENT_ID`
   - `GOOGLE_CLIENT_SECRET`
2. Get fresh credentials from [Google Cloud Console](https://console.cloud.google.com/)

---

### 3. ❌ "Column 'google_id' not found"

**Cause:** Database table missing the `google_id` column

**Solution:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `walkon_shoes` database
3. Click **SQL** tab
4. Run this command:
   ```sql
   ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL;
   ```
5. Click **Go**

---

### 4. ❌ Nothing happens when clicking "Sign in with Google"

**Possible Causes:**
- JavaScript error
- Button not linked properly
- Browser blocking popup

**Solutions:**
1. Open browser Developer Tools (F12)
2. Check **Console** tab for errors
3. Try in incognito/private mode
4. Disable browser extensions
5. Check if `google-login.php` file exists

---

### 5. ❌ Redirects to Google but then shows error

**Check these:**
1. **OAuth Consent Screen** is configured in Google Cloud Console
2. **Test users** are added (if app is in testing mode)
3. **Scopes** include:
   - `userinfo.email`
   - `userinfo.profile`

---

### 6. ❌ User created but not logged in

**Cause:** Session not being set properly

**Solution:**
Check `google-callback.php` lines 64-66 and 86-87:
```php
$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;
```

Make sure `session_start()` is at the top of `google-callback.php`.

---

## 🧪 Manual Testing Steps

1. **Test the OAuth flow:**
   - Click "Sign in with Google" on login page
   - Should redirect to Google consent screen
   - After authorization, should redirect back to your site
   - Should create user in database and log you in

2. **Check database:**
   ```sql
   SELECT id, first_name, last_name, email, google_id, is_verified 
   FROM users 
   WHERE google_id IS NOT NULL;
   ```

3. **Check Apache error logs:**
   - Location: `C:\xampp\apache\logs\error.log`
   - Look for PHP errors related to Google OAuth

---

## 📋 Checklist Before Asking for Help

- [ ] Ran `test-google-oauth.php` diagnostic
- [ ] cURL is enabled in PHP
- [ ] `google_id` column exists in `users` table
- [ ] Google Cloud Console redirect URI matches exactly
- [ ] OAuth consent screen is configured
- [ ] Client ID and Secret are correct
- [ ] Tested in incognito mode
- [ ] Checked browser console for errors
- [ ] Checked Apache error logs

---

## 🆘 Still Not Working?

If you've tried everything above:

1. **Check exact error message** in browser or `google-callback.php`
2. **Enable error display** in `google-callback.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
3. **Test with a simple cURL request** to verify network connectivity
4. **Try creating a new OAuth client** in Google Cloud Console

---

## ✅ Success Indicators

You'll know it's working when:
- ✓ Clicking "Sign in with Google" opens Google consent screen
- ✓ After authorization, you're redirected to dashboard
- ✓ New user appears in database with `google_id` populated
- ✓ You're logged in (session variables set)
- ✓ Can access protected pages like `dashboard.php`

---

**Need more help?** Run the diagnostic script and share the results!
