# Security Update Migration Guide

## Overview
This guide helps existing users migrate to the security-enhanced version of the PHP framework.

## Breaking Changes

### 1. Environment Configuration (Recommended)
**Old**: Configuration in `config/config.json`
**New**: Environment variables with fallback to JSON

**Migration Steps**:
1. Copy `.env.example` to `.env`
2. Set your database credentials in `.env`
3. Generate secure digest: `php -r "echo bin2hex(random_bytes(64));"`
4. Update your deployment scripts to use environment variables

### 2. Input Handling (Backward Compatible)
**Old**: `$request->input('field')` returned raw input
**New**: `$request->input('field')` returns sanitized input

**Migration Steps**:
- Most code will work without changes
- If you need raw input, use `$request->raw_input('field')`
- Review any custom input validation

### 3. CSRF Protection (New Feature)
**New**: CSRF tokens required for form submissions

**Migration Steps**:
1. Add CSRF token to forms:
   ```php
   <?= CsrfToken::field() ?>
   ```

2. Validate in controllers:
   ```php
   if (!CsrfToken::verifyRequest($request)) {
       throw new SecurityException('Invalid CSRF token');
   }
   ```

## Non-Breaking Improvements

### 1. Security Headers
Automatically applied to all responses - no code changes needed.

### 2. SQL Injection Prevention
Enhanced Model class with better validation - existing code works unchanged.

### 3. Session Security
Improved encryption automatically applied - existing sessions will be regenerated.

### 4. Error Handling
Database errors now logged instead of displayed - check error logs for debugging.

## Quick Start Checklist

### For New Projects:
- [ ] Copy `.env.example` to `.env`
- [ ] Set database credentials in `.env`
- [ ] Generate secure digest value
- [ ] Add CSRF tokens to forms
- [ ] Review the `SECURITY.md` guide

### For Existing Projects:
- [ ] Backup your application
- [ ] Test in development environment
- [ ] Review forms for CSRF token requirement
- [ ] Update deployment scripts for environment variables
- [ ] Check error logs after deployment

## Common Issues & Solutions

### Issue: "Invalid CSRF token" errors
**Solution**: Ensure forms include `<?= CsrfToken::field() ?>` and controllers validate tokens.

### Issue: Sessions not working
**Solution**: Ensure session is started and proper encryption key is set.

### Issue: Database connection errors
**Solution**: Check environment variables or fallback to JSON configuration.

### Issue: Input sanitization too aggressive
**Solution**: Use `$request->raw_input()` for specific fields that need raw data.

## Testing Your Migration

1. **Database Operations**: Test all CRUD operations
2. **Form Submissions**: Verify CSRF tokens work
3. **Session Management**: Test login/logout functionality
4. **Error Handling**: Check logs for proper error recording
5. **Security Headers**: Verify headers are set using browser dev tools

## Support

For issues during migration:
1. Check the `SECURITY.md` documentation
2. Review error logs for specific issues
3. Test with the minimal test cases provided
4. Open an issue with specific error details

## Security Benefits

After migration, your application will have:
- ✅ Protection against SQL injection
- ✅ XSS prevention
- ✅ CSRF protection
- ✅ Secure session management
- ✅ Modern encryption
- ✅ Security headers
- ✅ Improved error handling
- ✅ Directory traversal prevention