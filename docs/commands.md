# ⌨️ Artisan Security Commands

CyberShield provides a comprehensive suite of Artisan commands for security auditing, project scanning, real-time observability, and infrastructure management.

---

## Command Reference Index

| Command | Category | Description |
|---------|----------|-------------|
| [`security:scan`](#securityscan) | Audit | Static code analysis for vulnerabilities |
| [`security:dynamic-scan`](#securitydynamic-scan) | Audit | Behavioral & logic flow analysis |
| [`security:base`](#securitybase) | Lifecycle | Initialize and manage CyberShield |
| [`security:list-middleware`](#securitylist-middleware) | Introspection | List all registered security middleware |

---

## `security:scan`

The primary security auditor. Performs multi-rule static analysis of your codebase to identify vulnerabilities across 11 specialized rule engines.

### Syntax
```bash
php artisan security:scan [options]
```

### Options

| Option | Default | Description |
|--------|---------|-------------|
| `--path=` | `.` (project root) | Target directory for the scan. |
| `--severity=` | `low` | Minimum severity to report: `low`, `medium`, `high`, `critical`. |
| `--json` | `false` | Output results as JSON (for CI/CD). |
| `--no-ansi` | `false` | Disable colored output (for log files). |

### Examples

**Development scan — scan controllers only:**
```bash
php artisan security:scan --path=app/Http/Controllers --severity=low
```

**CI/CD pipeline — fail build on high-severity issues:**
```bash
php artisan security:scan --severity=high --json > security-report.json

# In your CI script:
php artisan security:scan --severity=high && echo "✅ Security passed" || exit 1
```

**Production pre-deployment check:**
```bash
php artisan security:scan --severity=medium --no-ansi
```

### What Gets Scanned

The scanner applies 11 specialized rule engines sequentially:

| Rule Engine | Checks For |
|-------------|------------|
| `MalwareRule` | `eval()`, `base64_decode()`, `shell_exec()`, obfuscated code patterns |
| `SqlInjectionRule` | Raw `DB::statement()` calls, unparameterized queries, `$_GET`/`$_POST` in queries |
| `XssRule` | Unescaped `{!! !!}` in Blade, `echo` without `htmlspecialchars` |
| `ConfigRule` | `APP_DEBUG=true` in production, exposed `.env`, missing `APP_KEY` |
| `DependencyRule` | Known vulnerable Composer packages (from CVE database) |
| `ModelSecurityRule` | Models with `$guarded = []` or without `$fillable` defined |
| `FileUploadRule` | Uploads without MIME type validation, executable extension checks |
| `BotDetectionRule` | Missing rate limiting on public forms, no honeypot on registration |
| `ApiSecurityRule` | API routes without authentication, missing rate limiting |
| `AuthSecurityRule` | Weak password validation, sessions without regeneration |
| `InfrastructureRule` | Open debug ports, insecure file permissions, exposed sensitive paths |

### Sample Output
```
🛡️  CyberShield Security Scan
Scanning: /var/www/myapp
Severity threshold: medium

  Rule: MalwareRule .............. ✅  0 issues
  Rule: SqlInjectionRule ......... ⚠️  2 issues
  Rule: XssRule .................. ✅  0 issues
  Rule: ConfigRule ............... 🚨  1 critical issue
  Rule: DependencyRule ........... ⚠️  1 medium issue
  Rule: ModelSecurityRule ......... ✅  0 issues
  Rule: FileUploadRule ........... ✅  0 issues
  Rule: BotDetectionRule ......... ⚠️  1 issue
  Rule: ApiSecurityRule .......... ✅  0 issues
  Rule: AuthSecurityRule ......... ✅  0 issues
  Rule: InfrastructureRule ........ ✅  0 issues

📋  FINDINGS SUMMARY
═══════════════════════════════════════════════════════

🚨  CRITICAL  [ConfigRule]
    APP_DEBUG is set to true in your .env file.
    File: .env:3
    Risk: Exposes detailed stack traces to end users.

⚠️  MEDIUM  [SqlInjectionRule]
    Raw query with unvalidated input detected.
    File: app/Http/Controllers/ReportController.php:45
    Snippet: DB::select("SELECT * FROM users WHERE id = " . $id)

⚠️  MEDIUM  [DependencyRule]
    Package 'league/flysystem' ^1.0 has known CVE-2021-32708.
    Action: Run `composer update league/flysystem`

Total: 1 critical, 2 medium issues found.
```

### Scheduled Usage

Add to `routes/console.php` or `app/Console/Kernel.php` for automated weekly audits:
```php
Schedule::command('security:scan --severity=medium')
    ->weeklyOn(0, '02:00')
    ->emailOutputTo('devops@yourcompany.com');
```

---

## `security:dynamic-scan`

Performs behavioral and logical flow analysis beyond what static text matching can find.

### Syntax
```bash
php artisan security:dynamic-scan [options]
```

### What It Analyzes

| Analysis Type | Description |
|--------------|-------------|
| **Logic Bombs** | Code that executes based on time, date, or counter conditions |
| **Hidden Egress** | Outbound HTTP calls or file writes in unexpected places |
| **Supply Chain Backdoors** | 3rd-party vendor code with unusual network calls |
| **Obfuscated Execution** | Base64-decoded strings passed to `eval()` |
| **Environment Sniffing** | Code checking for CI environment variables (deploy-time attacks) |

### Example
```bash
php artisan security:dynamic-scan --path=vendor/suspicious-package
```

> [!WARNING]
> The dynamic scan can take several minutes on large codebases. Run it on a separate process or in your CI pipeline overnight.

---

## `security:base`

Lifecycle management for the CyberShield infrastructure.

### Syntax
```bash
php artisan security:base {action}
```

### Actions

#### `security:base init`
Full initialization: verifies database connectivity, runs pending CyberShield migrations, checks that the `security_logs` table is accessible, validates the signature files exist, and verifies config.

```bash
php artisan security:base init
```

**Output:**
```
🛡️  CyberShield Initialization
  ✅  Database connection: OK
  ✅  Migrations: security_logs, ip_activity, request_logs — Applied
  ✅  Signature files: sql_injection.json, xss.json, rce.json — Loaded
  ✅  Config: config/cybershield.php — Valid
  ✅  Cache driver: redis — Connected

CyberShield is ready! Mode: active
```

---

## `security:list-middleware`

Returns a color-coded table of all 200+ registered CyberShield middleware guards, their category, and their alias.

### Syntax
```bash
php artisan security:list-middleware [options]
```

### Options

| Option | Description |
|--------|-------------|
| `--category=` | Filter by category: `request`, `rate`, `bot`, `network`, `auth`, `api`, `threat`, `monitoring` |
| `--json` | Output as JSON |

### Example
```bash
php artisan security:list-middleware --category=bot
```

**Output:**
```
🛡️  CyberShield Middleware Registry
Category: bot (25 middlewares)

  Alias                                    | Category       | Status
 ──────────────────────────────────────────|────────────────|──────────
  cybershield.detect_bot_traffic           | Bot Protection | ✅ Enabled
  cybershield.detect_headless_browser_bot  | Bot Protection | ✅ Enabled
  cybershield.detect_scraper_bot           | Bot Protection | ✅ Enabled
  cybershield.detect_automation_script     | Bot Protection | ✅ Enabled
  ...
```

---

## 🚦 Quick Command Reference Card

```bash
# ─── Project Security ────────────────────────────────────────────────────────
php artisan security:scan                          # Full scan, low severity
php artisan security:scan --severity=high          # High-severity only  
php artisan security:scan --path=app/Http          # Scan specific directory
php artisan security:scan --json                   # Machine-readable output
php artisan security:dynamic-scan                  # Behavioral analysis

# ─── Lifecycle ───────────────────────────────────────────────────────────────
php artisan security:base init                     # Initialize infrastructure
php artisan vendor:publish --tag=cybershield-config    # Publish config
php artisan vendor:publish --tag=cybershield-migrations # Publish migrations
php artisan migrate                                # Run database migrations

# ─── Introspection ───────────────────────────────────────────────────────────
php artisan security:list-middleware               # All 200+ middleware
php artisan security:list-middleware --category=api # Filter by category
```

---

## 🔄 CI/CD Integration

### GitHub Actions Example
```yaml
name: Security Scan

on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install dependencies
        run: composer install --no-dev
      
      - name: Run CyberShield Security Scan
        run: php artisan security:scan --severity=high --json --no-ansi
        env:
          APP_KEY: ${{ secrets.APP_KEY }}
          DB_CONNECTION: sqlite
          DB_DATABASE: ":memory:"
```

### Scheduled Audit in Console Kernel
```php
// routes/console.php (Laravel 11+)
use Illuminate\Support\Facades\Schedule;

Schedule::command('security:scan --severity=medium')
    ->weekly()
    ->at('02:00')
    ->emailOutputTo('security@yourdomain.com');

Schedule::command('security:dynamic-scan')
    ->monthly()
    ->at('03:00');
```

[← Back to Blade Directives](blade-directives.md) | [Next: Logging →](logging.md)
