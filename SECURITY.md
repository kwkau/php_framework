# Security Guide for PHP Framework

## Overview
This document outlines the security measures implemented in the PHP framework and best practices for secure development.

## Security Features Implemented

### 1. SQL Injection Prevention
- **Prepared Statements**: All database queries use prepared statements with parameter binding
- **Input Validation**: Column names and SQL operations are validated against whitelists
- **Query Sanitization**: Table and column names are sanitized to prevent injection

### 2. Cross-Site Scripting (XSS) Prevention
- **Input Sanitization**: All user input is sanitized using `htmlspecialchars()`
- **Output Encoding**: Data is encoded when displayed to prevent script execution
- **Content Security Policy**: Basic CSP headers are set to prevent inline script execution

### 3. Cross-Site Request Forgery (CSRF) Protection
- **CSRF Tokens**: Use `CsrfToken` class to generate and validate tokens
- **Form Protection**: Include CSRF tokens in all forms using `CsrfToken::field()`
- **Request Validation**: Validate tokens on form submissions

### 4. Session Security
- **Secure Session Configuration**: Sessions use secure cookies and HTTP-only flags
- **Session Regeneration**: Session IDs are regenerated on login
- **Modern Encryption**: OpenSSL encryption replaces deprecated mcrypt

### 5. Security Headers
- **X-Frame-Options**: Prevents clickjacking attacks
- **X-Content-Type-Options**: Prevents MIME sniffing
- **X-XSS-Protection**: Browser XSS protection enabled
- **Strict-Transport-Security**: HTTPS enforcement when available

### 6. Configuration Security
- **Environment Variables**: Sensitive configuration moved to environment variables
- **Secure Defaults**: Default configuration uses secure settings
- **Error Handling**: Database errors are logged but not exposed to users

## Security Best Practices

### 1. Environment Setup
```bash
# Copy environment template
cp .env.example .env

# Set secure database credentials
DB_PASS=your_secure_password_here

# Generate secure digest
php -r "echo bin2hex(random_bytes(64));"
```

### 2. CSRF Protection Usage
```php
// In forms
<?= CsrfToken::field() ?>

// In controllers
public function update(Request $request) {
    if (!CsrfToken::verifyRequest($request)) {
        throw new SecurityException('Invalid CSRF token');
    }
    // Process request...
}
```

### 3. Input Validation
```php
// Sanitized input (default)
$email = $request->input('email');

// Raw input (use with caution)
$data = $request->raw_input('data');
```

### 4. Database Operations
```php
// Secure - uses prepared statements
$users = User::where('email', '=', $email);

// Secure - input is validated
$user = User::instance()->populate($request->json_content())->insert();
```

## Security Checklist

### Development
- [ ] Use environment variables for sensitive configuration
- [ ] Implement CSRF protection on all forms
- [ ] Validate and sanitize all user input
- [ ] Use prepared statements for all database queries
- [ ] Implement proper error handling
- [ ] Set security headers

### Production
- [ ] Use HTTPS for all communications
- [ ] Set secure database passwords
- [ ] Configure proper file permissions
- [ ] Enable error logging but disable display
- [ ] Regular security updates
- [ ] Monitor for suspicious activity

## Common Vulnerabilities to Avoid

1. **SQL Injection**: Always use prepared statements
2. **XSS**: Sanitize all output and input
3. **CSRF**: Implement token validation
4. **Session Hijacking**: Use secure session configuration
5. **Directory Traversal**: Validate file paths
6. **Information Disclosure**: Handle errors securely

## Reporting Security Issues

If you discover a security vulnerability, please report it privately to the maintainers.

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [Secure Coding Practices](https://wiki.sei.cmu.edu/confluence/display/seccode)