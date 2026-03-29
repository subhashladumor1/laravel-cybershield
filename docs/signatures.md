# 🛡️ WAF Signatures & Pattern Recognition

The CyberShield Web Application Firewall (WAF) uses a high-performance, JSON-distributed **Intelligence Database** to categorize and neutralize incoming threats.

## 1. How the Brain Works: Signature Analysis
CyberShield doesn't just block IPs; it classifies **intent**. Each signature corresponds to a specific attack vector.

### The Inspection Chain
1. **Targeting**: The WAF extracts raw data from 4 key areas (URI, Query, Body, Headers).
2. **De-Obfuscation**: Payloads are normalized (whitespace removed, hex-decoding performed).
3. **Regex Application**: Signatures are applied in order of severity.
4. **Contextual Score**: A match doesn't just block; it adds to the **Cumulative Risk Score**.

---

## 🧠 The Signature Schema

Signatures are designed to be human-readable but machine-fast.

```json
{
    "name": "Remote Code Execution (RCE)",
    "patterns": [
        "shell_exec\\(",
        "passthru\\(",
        "system\\(",
        "base64_decode\\("
    ],
    "severity": "critical",
    "category": "rce"
}
```

### Severity Impact
- **Low**: Adds 10-20 to Threat Score.
- **Medium**: Adds 40-50 to Threat Score (Possible Challenge).
- **High**: Adds 100 to Threat Score (Immediate 24h Block).
- **Critical**: Adds 100 to Threat Score + Immediate 30-Day Quarantine.

---

## 🛡️ Pro-Active Evasion Prevention
Sophisticated attackers use encoding (Base64, URL-Encoding) to bypass simple firewalls. CyberShield's `WAFEngine` performs **Recursive Decoding**:
If it sees a URL-encoded string, it decodes it and *re-scans* the result. This catches attacks like `%3Cscript%3E` (URL-encoded `<script>`).

---

## 🛠️ Adding Custom Intelligence
Keep your custom signatures in a separate file to ensure they aren't overwritten during package updates.

```php
// config/cybershield.php
'signatures' => [
    'custom' => base_path('security/firewall/local_rules.json'),
]
```

### Pro-Tip: Signature Testing
Before adding a signature to production, run it against your `security:scan` tool to see if it triggers false positives in your own codebase.

[Back to Monitoring](monitoring.md) | [Go to Threats & Scoring](threats.md)
