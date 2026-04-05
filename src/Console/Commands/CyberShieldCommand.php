<?php

namespace CyberShield\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * php artisan cybershield
 *
 * Interactive Security Control Panel — GUI-style terminal interface.
 */
class CyberShieldCommand extends Command
{
    protected $signature   = 'cybershield';
    protected $description = '🛡️  Open the CyberShield interactive security control panel';

    // ─── Named color themes ─────────────────────────────────────────────────
    private static array $themes = [
        'MATRIX'  => ['acc' => 'green',   'hi' => 'green',   'tag' => 'black', 'tagbg' => 'green',   'dim' => 'gray'],
        'PLASMA'  => ['acc' => 'cyan',    'hi' => 'white',   'tag' => 'black', 'tagbg' => 'cyan',    'dim' => 'gray'],
        'FLAME'   => ['acc' => 'red',     'hi' => 'yellow',  'tag' => 'white', 'tagbg' => 'red',     'dim' => 'gray'],
        'AURORA'  => ['acc' => 'magenta', 'hi' => 'cyan',    'tag' => 'black', 'tagbg' => 'magenta', 'dim' => 'gray'],
        'SOLAR'   => ['acc' => 'yellow',  'hi' => 'white',   'tag' => 'black', 'tagbg' => 'yellow',  'dim' => 'gray'],
        'ICE'     => ['acc' => 'blue',    'hi' => 'cyan',    'tag' => 'white', 'tagbg' => 'blue',    'dim' => 'gray'],
    ];

    // ─── Random taglines ────────────────────────────────────────────────────
    private static array $taglines = [
        '⚡ Enterprise-Grade Security for Laravel Applications',
        '🛡️  Protect · Detect · Respond — Battle-Tested Defense',
        '🔒 Active Threat Intelligence · Full-Stack Security Kernel',
        '🚀 Real-Time WAF · Bot Defense · Rate Limiter · Geo-Blocking',
        '🎯 Developer-First Security — 150+ Artisan Commands Available',
        '🌐 Zero-Trust Architecture · Powered by CyberShield v2.0',
    ];

    // ─── Large ASCII banners ─────────────────────────────────────────────────
    private static array $banners = [
        // ① BIG BLOCK — matches the lab terminal pixel style
        [
            '  ██████╗██╗   ██╗██████╗ ███████╗██████╗ ███████╗██╗  ██╗██╗███████╗██╗     ██████╗ ',
            ' ██╔════╝╚██╗ ██╔╝██╔══██╗██╔════╝██╔══██╗██╔════╝██║  ██║██║██╔════╝██║     ██╔══██╗',
            ' ██║      ╚████╔╝ ██████╔╝█████╗  ██████╔╝███████╗███████║██║█████╗  ██║     ██║  ██║',
            ' ██║       ╚██╔╝  ██╔══██╗██╔══╝  ██╔══██╗╚════██║██╔══██║██║██╔══╝  ██║     ██║  ██║',
            ' ╚██████╗   ██║   ██████╔╝███████╗██║  ██║███████║██║  ██║██║███████╗███████╗██████╔╝ ',
            '  ╚═════╝   ╚═╝   ╚═════╝ ╚══════╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚═╝╚══════╝╚══════╝╚═════╝  ',
        ],
        // ② SHADOW — filled block variant
        [
            ' ░█████╗░██╗░░░██╗██████╗░███████╗██████╗░░██████╗██╗░░██╗██╗███████╗██╗░░░░░██████╗░',
            ' ██╔══██╗╚██╗░██╔╝██╔══██╗██╔════╝██╔══██╗██╔════╝██║░░██║██║██╔════╝██║░░░░░██╔══██╗',
            ' ██║░░╚═╝░╚████╔╝░██████╦╝█████╗░░██████╔╝╚█████╗░███████║██║█████╗░░██║░░░░░██║░░██║',
            ' ██║░░██╗░░╚██╔╝░░██╔══██╗██╔══╝░░██╔══██╗░╚═══██╗██╔══██║██║██╔══╝░░██║░░░░░██║░░██║',
            ' ╚█████╔╝░░░██║░░░██████╦╝███████╗██║░░██║██████╔╝██║░░██║██║███████╗███████╗██████╔╝',
            ' ░╚════╝░░░░╚═╝░░░╚═════╝░╚══════╝╚═╝░░╚═╝╚═════╝░╚═╝░░╚═╝╚═╝╚══════╝╚══════╝╚═════╝░',
        ],
        // ③ FRAMED — with decorative border
        [
            '  ┌─────────────────────────────────────────────────────────────────┐',
            '  │  ██████╗██╗   ██╗██████╗ ███████╗██████╗ ███████╗██╗  ██╗██╗   │',
            '  │ ██╔════╝╚██╗ ██╔╝██╔══██╗██╔════╝██╔══██╗██╔════╝██║  ██║██║   │',
            '  │ ██║      ╚████╔╝ ██████╔╝█████╗  ██████╔╝███████╗███████║██║   │',
            '  │ ╚██████╗   ██║   ██████╔╝███████╗██║  ██║███████║██║  ██║██║   │',
            '  │  ╚═════╝   ╚═╝   ╚══════╝╚══════╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚═╝   │',
            '  │              S H I E L D  v 2 . 0 . 0                            │',
            '  └─────────────────────────────────────────────────────────────────┘',
        ],
    ];

    // ─── Main Menu Definition ────────────────────────────────────────────────
    private static array $menu = [
        '1' => ['label' => 'Security Scan',       'desc' => 'Run scans against your application',     'icon' => '⚡', 'sub' => 'scan'],
        '2' => ['label' => 'Security Reports',    'desc' => 'Generate detailed security reports',      'icon' => '📊', 'sub' => 'report'],
        '3' => ['label' => 'WAF & Firewall',      'desc' => 'Web application firewall diagnostics',    'icon' => '🛡️', 'sub' => 'waf'],
        '4' => ['label' => 'Bot & Rate Limiting', 'desc' => 'Bot detection and traffic analysis',      'icon' => '🤖', 'sub' => 'bot'],
        '5' => ['label' => 'Network & Geo',       'desc' => 'SSL, headers, geo-blocking checks',       'icon' => '🌐', 'sub' => 'net'],
        '6' => ['label' => 'API & Auth Security', 'desc' => 'Authentication and API security audit',   'icon' => '🔑', 'sub' => 'auth'],
        '7' => ['label' => 'Full Security Audit', 'desc' => 'Run all security modules at once',        'icon' => '🎯', 'cmd' => 'security:scan:full'],
        '8' => ['label' => 'List All Middleware', 'desc' => 'Show all registered middleware aliases',  'icon' => '📋', 'cmd' => 'cybershield:list-middleware'],
        '9' => ['label' => 'Quick Reference',     'desc' => 'Command cheat sheet & usage tips',        'icon' => '📖', 'sub' => 'help'],
        '0' => ['label' => 'Exit Control Panel',  'desc' => 'Exit the CyberShield terminal',           'icon' => '✖',  'exit' => true],
    ];

    private static array $subMenus = [
        'scan' => [
            'title' => 'Security Scan Commands',
            'icon'  => '⚡',
            'desc'  => 'Comprehensive vulnerability scanning across 13 categories',
            'items' => [
                '1'  => ['cmd' => 'security:scan:quick',        'label' => 'Quick Scan',           'desc' => 'Fast critical-only check (~30s)'],
                '2'  => ['cmd' => 'security:scan:full',         'label' => 'Full Audit',           'desc' => 'All modules, thorough scan (~5min)'],
                '3'  => ['cmd' => 'security:scan:deep',         'label' => 'Deep Scan',            'desc' => 'Includes obfuscated/encoded code'],
                '4'  => ['cmd' => 'security:scan:production',   'label' => 'Production Check',     'desc' => 'Pre-deployment checklist'],
                '5'  => ['cmd' => 'security:scan:malware',      'label' => 'Malware Detection',    'desc' => 'Webshells, backdoors, trojan patterns'],
                '6'  => ['cmd' => 'security:scan:sql',          'label' => 'SQL Injection',        'desc' => 'Raw queries & injection vectors'],
                '7'  => ['cmd' => 'security:scan:xss',          'label' => 'XSS Vulnerability',   'desc' => 'Cross-site scripting patterns'],
                '8'  => ['cmd' => 'security:scan:dependencies', 'label' => 'Dependency Audit',     'desc' => 'CVE & advisory check via composer'],
                '9'  => ['cmd' => 'security:scan:env',          'label' => 'Environment Audit',    'desc' => '.env exposure & config leaks'],
                '10' => ['cmd' => 'security:scan:auth',         'label' => 'Auth Security',        'desc' => 'Password, session, 2FA coverage'],
                '11' => ['cmd' => 'security:scan:file-upload',  'label' => 'File Upload',          'desc' => 'Upload validation & extension check'],
                '12' => ['cmd' => 'security:scan:fix',          'label' => 'Auto-Fix Issues',      'desc' => 'Attempt to remediate detected issues'],
            ],
        ],
        'report' => [
            'title' => 'Security Report Commands',
            'icon'  => '📊',
            'desc'  => 'Export and view security posture reports',
            'items' => [
                '1' => ['cmd' => 'security:report:summary',         'label' => 'Summary',          'desc' => 'Concise security posture overview'],
                '2' => ['cmd' => 'security:report:dashboard',       'label' => 'Dashboard',        'desc' => 'Visual dashboard-style overview'],
                '3' => ['cmd' => 'security:report:audit',           'label' => 'Full Audit',       'desc' => 'Detailed findings report'],
                '4' => ['cmd' => 'security:report:threats',         'label' => 'Threats',          'desc' => 'Active & historical threat report'],
                '5' => ['cmd' => 'security:report:vulnerabilities', 'label' => 'Vulnerabilities',  'desc' => 'Known CVE & package vulnerabilities'],
                '6' => ['cmd' => 'security:report:logs',            'label' => 'Logs Analysis',    'desc' => 'Security log aggregation report'],
                '7' => ['cmd' => 'security:report:json',            'label' => 'Export JSON',      'desc' => 'Machine-readable JSON export'],
                '8' => ['cmd' => 'security:report:html',            'label' => 'Export HTML',      'desc' => 'Human-readable HTML report'],
                '9' => ['cmd' => 'security:report',                 'label' => 'Full Report',      'desc' => 'All formats at once'],
            ],
        ],
        'waf' => [
            'title' => 'WAF & Firewall Diagnostics',
            'icon'  => '🛡️',
            'desc'  => 'Web Application Firewall rules, injection patterns, shell detection',
            'items' => [
                '1' => ['cmd' => 'security:scan:firewall',      'label' => 'WAF Rules',          'desc' => 'Verify firewall rule coverage'],
                '2' => ['cmd' => 'security:scan:sql-injection', 'label' => 'SQL Deep Scan',      'desc' => 'Thorough SQL pattern analysis'],
                '3' => ['cmd' => 'security:scan:xss',           'label' => 'XSS Deep Scan',      'desc' => 'DOM-based & reflected XSS'],
                '4' => ['cmd' => 'security:scan:eval-usage',    'label' => 'Dangerous Fns',      'desc' => 'eval(), exec(), system() usage'],
                '5' => ['cmd' => 'security:scan:shell-execution','label' => 'Shell Execution',   'desc' => 'OS command execution patterns'],
                '6' => ['cmd' => 'security:scan:webshell',      'label' => 'Web Shells',         'desc' => 'PHP webshell signatures'],
                '7' => ['cmd' => 'security:scan:backdoor',      'label' => 'Backdoors',          'desc' => 'Hidden access point detection'],
                '8' => ['cmd' => 'security:scan:obfuscated-code','label' => 'Obfuscated Code',  'desc' => 'Minified & encoded malicious code'],
            ],
        ],
        'bot' => [
            'title' => 'Bot Defense & Rate Limiting',
            'icon'  => '🤖',
            'desc'  => 'Traffic analysis, DDoS patterns, headless browsers, scrapers',
            'items' => [
                '1' => ['cmd' => 'security:scan:bot',            'label' => 'Bot Analysis',       'desc' => 'Overall bot traffic audit'],
                '2' => ['cmd' => 'security:scan:bot-traffic',    'label' => 'Traffic Volume',     'desc' => 'Requests per IP analysis'],
                '3' => ['cmd' => 'security:scan:automation',     'label' => 'Browser Headless',   'desc' => 'Puppeteer/Playwright detection'],
                '4' => ['cmd' => 'security:scan:fake-browser',   'label' => 'Fake User-Agents',   'desc' => 'Spoofed UA string detection'],
                '5' => ['cmd' => 'security:scan:scraper',        'label' => 'Web Scrapers',       'desc' => 'Content scraping detection'],
                '6' => ['cmd' => 'security:scan:ddos-pattern',   'label' => 'DDoS Patterns',      'desc' => 'Flood & burst attack detection'],
                '7' => ['cmd' => 'security:scan:traffic-anomaly','label' => 'Traffic Anomaly',    'desc' => 'Spike & variance detection'],
                '8' => ['cmd' => 'security:scan:api-rate-limit', 'label' => 'API Rate Limits',    'desc' => 'Rate limiting coverage check'],
            ],
        ],
        'net' => [
            'title' => 'Network & Geo Security',
            'icon'  => '🌐',
            'desc'  => 'TLS, SSL, HTTP headers, server exposure, port scanning',
            'items' => [
                '1' => ['cmd' => 'security:scan:ssl',              'label' => 'SSL/TLS Cert',       'desc' => 'Certificate validity & strength'],
                '2' => ['cmd' => 'security:scan:tls',              'label' => 'TLS Protocols',      'desc' => 'TLS 1.0/1.1 deprecation check'],
                '3' => ['cmd' => 'security:scan:security-headers', 'label' => 'Security Headers',   'desc' => 'HSTS, CSP, X-Frame-Options'],
                '4' => ['cmd' => 'security:scan:server-headers',   'label' => 'Server Leakage',     'desc' => 'Server version info exposure'],
                '5' => ['cmd' => 'security:scan:ports',            'label' => 'Open Ports',         'desc' => 'Unexpected open port detection'],
                '6' => ['cmd' => 'security:scan:server',           'label' => 'Server Config',      'desc' => 'Web server security config'],
                '7' => ['cmd' => 'security:scan:firewall',         'label' => 'Firewall Rules',     'desc' => 'WAF configuration check'],
                '8' => ['cmd' => 'security:scan:filesystem-permissions', 'label' => 'Fs Perms',    'desc' => 'File & directory permission tree'],
            ],
        ],
        'auth' => [
            'title' => 'API & Authentication Security',
            'icon'  => '🔑',
            'desc'  => 'Sessions, tokens, 2FA, API endpoints, OAuth/Sanctum coverage',
            'items' => [
                '1' => ['cmd' => 'security:scan:auth',           'label' => 'Auth Audit',         'desc' => 'Full authentication vulnerability scan'],
                '2' => ['cmd' => 'security:scan:password',       'label' => 'Password Policy',    'desc' => 'Hashing, min length, entropy'],
                '3' => ['cmd' => 'security:scan:2fa',            'label' => '2FA Coverage',       'desc' => 'Two-factor authentication check'],
                '4' => ['cmd' => 'security:scan:session',        'label' => 'Session Security',   'desc' => 'Session fixation & hijacking'],
                '5' => ['cmd' => 'security:scan:api',            'label' => 'API Endpoints',      'desc' => 'All API routes & exposure'],
                '6' => ['cmd' => 'security:scan:api-auth',       'label' => 'API Auth',           'desc' => 'Token & OAuth mechanism audit'],
                '7' => ['cmd' => 'security:scan:api-exposure',   'label' => 'Data Exposure',      'desc' => 'Sensitive field leakage check'],
                '8' => ['cmd' => 'security:scan:token',          'label' => 'Token Security',     'desc' => 'CSRF & API token analysis'],
                '9' => ['cmd' => 'security:scan:api-rate-limit', 'label' => 'API Rate Limit',     'desc' => 'Rate limiting on API routes'],
            ],
        ],
    ];

    private string $themeName = 'PLASMA';
    private array  $theme     = ['acc' => 'cyan', 'hi' => 'white', 'tag' => 'black', 'tagbg' => 'cyan', 'dim' => 'gray'];

    // ────────────────────────────────────────────────────────────────────────
    public function handle(): int
    {
        // Pick random theme
        $themeNames      = array_keys(self::$themes);
        $this->themeName = $themeNames[array_rand($themeNames)];
        $this->theme     = self::$themes[$this->themeName];

        $this->registerStyles();
        $this->printBanner();
        $this->printSystemStatus();
        $this->runMainMenu();

        $this->line('');
        $this->line("  <acc>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</acc>");
        $this->line("  <dim>  Session ended · Stay secure · CyberShield v2.0 · github.com/subhashladumor1/laravel-cybershield</dim>");
        $this->line("  <acc>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</acc>");
        $this->line('');

        return 0;
    }

    // ─── Style registration ──────────────────────────────────────────────────
    private function registerStyles(): void
    {
        $t = $this->theme;
        $f = $this->output->getFormatter();
        $f->setStyle('acc',  new OutputFormatterStyle($t['acc'],   null,       ['bold']));
        $f->setStyle('accbg',new OutputFormatterStyle($t['tag'],   $t['tagbg'],['bold']));
        $f->setStyle('hi',   new OutputFormatterStyle($t['hi'],    null,       ['bold']));
        $f->setStyle('dim',  new OutputFormatterStyle('gray',      null,       []));
        $f->setStyle('ok',   new OutputFormatterStyle('green',     null,       ['bold']));
        $f->setStyle('warn', new OutputFormatterStyle('yellow',    null,       ['bold']));
        $f->setStyle('fail', new OutputFormatterStyle('red',       null,       ['bold']));
        $f->setStyle('box',  new OutputFormatterStyle($t['acc'],   null,       []));
        $f->setStyle('num',  new OutputFormatterStyle('yellow',    null,       ['bold']));
        $f->setStyle('lbl',  new OutputFormatterStyle('white',     null,       []));
        $f->setStyle('gdim', new OutputFormatterStyle('gray',      null,       ['bold']));
    }

    // ─── Banner ──────────────────────────────────────────────────────────────
    private function printBanner(): void
    {
        $banner  = self::$banners[array_rand(self::$banners)];
        $tagline = self::$taglines[array_rand(self::$taglines)];

        $this->line('');
        foreach ($banner as $line) {
            $this->line("  <acc>{$line}</acc>");
        }
        $this->line('');
        $this->line(
            "  <accbg>  ⚡ {$this->themeName} MODE  </accbg>" .
            "  <accbg>  SECURITY CONTROL PANEL  </accbg>" .
            "  <dim>Laravel " . app()->version() . " · PHP " . PHP_VERSION . "</dim>"
        );
        $this->line("  <acc>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</acc>");
        $this->line("  <dim>  {$tagline}</dim>");
        $this->line("  <acc>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</acc>");
        $this->line('');
    }

    // ─── System status bar ──────────────────────────────────────────────────
    private function printSystemStatus(): void
    {
        $modules = [
            ['WAF Firewall',    '✔ ACTIVE', 'ok'],
            ['Rate Limiter',    '✔ ACTIVE', 'ok'],
            ['Bot Defense',     '✔ ACTIVE', 'ok'],
            ['Network Guard',   '✔ ACTIVE', 'ok'],
            ['API Security',    '✔ ACTIVE', 'ok'],
            ['Security Scanner','✔ ACTIVE', 'ok'],
        ];

        $this->line("  <box>┌" . str_repeat('─', 78) . "┐</box>");
        $this->line("  <box>│</box>  <hi>◈  SYSTEM STATUS</hi>                                                              <box>│</box>");
        $this->line("  <box>├" . str_repeat('─', 78) . "┤</box>");

        // Two-column layout
        $half  = (int)ceil(count($modules) / 2);
        $left  = array_slice($modules, 0, $half);
        $right = array_slice($modules, $half);

        foreach ($left as $i => $mod) {
            [$lName, $lStatus, $lColor] = $mod;
            $rMod = $right[$i] ?? null;
            $lPad = str_repeat(' ', max(1, 16 - mb_strlen($lName)));

            if ($rMod) {
                [$rName, $rStatus, $rColor] = $rMod;
                $rPad = str_repeat(' ', max(1, 16 - mb_strlen($rName)));
                $this->line(
                    "  <box>│</box>  <hi>{$lName}</hi>{$lPad}<{$lColor}>{$lStatus}</{$lColor}>               " .
                    "<hi>{$rName}</hi>{$rPad}<{$rColor}>{$rStatus}</{$rColor}>             <box>│</box>"
                );
            } else {
                $this->line("  <box>│</box>  <hi>{$lName}</hi>{$lPad}<{$lColor}>{$lStatus}</{$lColor}>" . str_repeat(' ', 50) . "<box>│</box>");
            }
        }

        $env = app()->environment();
        $debug = config('app.debug') ? '<warn>⚠ DEBUG ON</warn>' : '<ok>✔ DEBUG OFF</ok>';
        $this->line("  <box>├" . str_repeat('─', 78) . "┤</box>");
        $this->line("  <box>│</box>  <dim>Environment: <hi>{$env}</hi>   {$debug}   Run: <hi>php artisan cybershield</hi> anytime</dim>                 <box>│</box>");
        $this->line("  <box>└" . str_repeat('─', 78) . "┘</box>");
        $this->line('');
    }

    // ─── Main menu loop ──────────────────────────────────────────────────────
    private function runMainMenu(): void
    {
        while (true) {
            $this->printMainMenu();

            $choice = trim((string)($this->ask("  <acc>❯</acc> Enter option") ?? '0'));

            if (in_array($choice, ['0', 'exit', 'quit', 'q'], true)) break;

            if (!isset(self::$menu[$choice])) {
                $this->line("  <warn>⚠  Unknown option '{$choice}'. Enter a number shown in the menu.</warn>");
                $this->line('');
                continue;
            }

            $item = self::$menu[$choice];

            if (isset($item['exit'])) break;

            if (isset($item['cmd'])) {
                $this->runCmd($item['cmd']);
                $this->pause();
                continue;
            }

            if (isset($item['sub'])) {
                if ($item['sub'] === 'help') {
                    $this->printHelp();
                    $this->pause();
                } else {
                    $this->runSubMenu($item['sub']);
                }
            }
        }
    }

    // ─── Main menu display ───────────────────────────────────────────────────
    private function printMainMenu(): void
    {
        $this->line('');
        $this->line("  <box>╔" . str_repeat('═', 78) . "╗</box>");
        $this->line("  <box>║</box>  <hi>◈  MAIN MENU</hi>" . str_repeat(' ', 64) . "<box>║</box>");
        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");

        $items = self::$menu;
        // Remove '0' for last rendering
        $exitItem = $items['0'];
        unset($items['0']);

        foreach ($items as $key => $item) {
            $icon    = $item['icon'];
            $label   = $item['label'];
            $desc    = $item['desc'];
            $descPad = str_repeat(' ', max(1, 42 - mb_strlen($desc)));
            $numPad  = mb_strlen($key) === 1 ? ' ' : '';
            $this->line("  <box>║</box>  <num>[{$key}]</num>{$numPad}  {$icon}  <hi>{$label}</hi>  <dim>—  {$desc}</dim>{$descPad}<box>║</box>");
        }

        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");
        $this->line("  <box>║</box>  <num>[0]</num>  {$exitItem['icon']}  <hi>{$exitItem['label']}</hi>  <dim>—  {$exitItem['desc']}</dim>" . str_repeat(' ', 30) . "<box>║</box>");
        $this->line("  <box>╚" . str_repeat('═', 78) . "╝</box>");
        $this->line('');
    }

    // ─── Sub-menu ────────────────────────────────────────────────────────────
    private function runSubMenu(string $key): void
    {
        $sub = self::$subMenus[$key];

        while (true) {
            $this->printSubMenu($sub);

            $choice = trim((string)($this->ask("  <acc>❯</acc> Select command (or 0 to go back)") ?? '0'));

            if (in_array($choice, ['0', 'b', 'back'], true)) break;

            if (!isset($sub['items'][$choice])) {
                $this->line("  <warn>⚠  Invalid option.</warn>");
                continue;
            }

            $this->runCmd($sub['items'][$choice]['cmd']);
            $this->pause();
        }
    }

    private function printSubMenu(array $sub): void
    {
        $icon  = $sub['icon'];
        $title = $sub['title'];
        $desc  = $sub['desc'];

        $this->line('');
        $this->line("  <box>╔" . str_repeat('═', 78) . "╗</box>");
        $this->line("  <box>║</box>  <acc>{$icon}  {$title}</acc>" . str_repeat(' ', max(1, 73 - mb_strlen($title) - mb_strlen($icon))) . "<box>║</box>");
        $this->line("  <box>║</box>  <dim>{$desc}</dim>" . str_repeat(' ', max(1, 76 - mb_strlen($desc))) . "<box>║</box>");
        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");
        $this->line("  <box>║</box>  <gdim>  #   Command                               Description                 </gdim>  <box>║</box>");
        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");

        foreach ($sub['items'] as $num => $item) {
            $numPad  = mb_strlen((string)$num) === 1 ? ' ' : '';
            $cmdPad  = str_repeat(' ', max(1, 36 - mb_strlen($item['cmd'])));
            $descPad = str_repeat(' ', max(1, 30 - mb_strlen($item['desc'])));
            $this->line(
                "  <box>║</box>  <num>[{$num}]</num>{$numPad}  <acc>{$item['cmd']}</acc>{$cmdPad}<dim>{$item['desc']}</dim>{$descPad}<box>║</box>"
            );
        }

        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");
        $this->line("  <box>║</box>  <num>[0]</num>   <dim>← Back to main menu</dim>" . str_repeat(' ', 52) . "<box>║</box>");
        $this->line("  <box>╚" . str_repeat('═', 78) . "╝</box>");
        $this->line('');
    }

    // ─── Command executor ────────────────────────────────────────────────────
    private function runCmd(string $cmd): void
    {
        $this->line('');
        $this->line("  <box>┌" . str_repeat('─', 78) . "┐</box>");
        $this->line("  <box>│</box>  <hi>▶  Executing:</hi>  <acc>{$cmd}</acc>" . str_repeat(' ', max(1, 62 - mb_strlen($cmd))) . "<box>│</box>");
        $this->line("  <box>└" . str_repeat('─', 78) . "┘</box>");
        $this->line('');

        try {
            $this->call($cmd);
        } catch (\Throwable $e) {
            $this->line("  <fail>✗  Failed to execute '{$cmd}'</fail>");
            $this->line("  <dim>   {$e->getMessage()}</dim>");
        }
    }

    // ─── Quick reference help ─────────────────────────────────────────────────
    private function printHelp(): void
    {
        $commands = [
            ['security:scan:quick',          'Fast scan — critical vectors only'],
            ['security:scan:full',           'Full audit — all 13 scan modules'],
            ['security:scan:fix',            'Auto-fix remediable issues'],
            ['security:scan:malware',        'Webshell & backdoor detection'],
            ['security:report:summary',      'Concise security posture summary'],
            ['security:report:json',         'Export full report as JSON'],
            ['security:report:html',         'Export full report as HTML'],
            ['cybershield:list-middleware',  'List all aliases & middleware classes'],
            ['cybershield',                  'Open this control panel any time'],
        ];

        $tips = [
            'Append  --no-interaction  to skip prompts in CI/CD pipelines.',
            'Use     php artisan security:scan full  for maximum coverage.',
            'Reports in JSON format are ideal for SIEM integrations.',
            'Schedule  security:scan:quick  daily via Laravel scheduler.',
        ];

        $this->line('');
        $this->line("  <box>╔" . str_repeat('═', 78) . "╗</box>");
        $this->line("  <box>║</box>  <hi>📖  QUICK REFERENCE — All Key Commands</hi>" . str_repeat(' ', 39) . "<box>║</box>");
        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");

        foreach ($commands as [$cmd, $desc]) {
            $pad = str_repeat(' ', max(1, 38 - mb_strlen($cmd)));
            $dpd = str_repeat(' ', max(1, 36 - mb_strlen($desc)));
            $this->line("  <box>║</box>  <acc>php artisan {$cmd}</acc>{$pad}<dim>{$desc}</dim>{$dpd}<box>║</box>");
        }

        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");
        $this->line("  <box>║</box>  <hi>💡  Pro Tips</hi>" . str_repeat(' ', 66) . "<box>║</box>");
        $this->line("  <box>╠" . str_repeat('═', 78) . "╣</box>");

        foreach ($tips as $tip) {
            $tpad = str_repeat(' ', max(1, 76 - mb_strlen($tip)));
            $this->line("  <box>║</box>  <dim>→  {$tip}</dim>{$tpad}<box>║</box>");
        }

        $this->line("  <box>╚" . str_repeat('═', 78) . "╝</box>");
        $this->line('');
    }

    // ─── Pause helper ────────────────────────────────────────────────────────
    private function pause(): void
    {
        $this->line('');
        $this->line("  <dim>─────────────────────────────────────────────────────────────────────────────</dim>");
        $this->line("  <dim>  Press ENTER to return to the main menu…</dim>");
        $this->ask('');
    }
}
