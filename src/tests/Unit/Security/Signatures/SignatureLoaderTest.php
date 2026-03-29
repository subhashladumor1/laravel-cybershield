<?php

namespace CyberShield\Tests\Unit\Security\Signatures;

use CyberShield\Tests\TestCase;
use CyberShield\Security\Signatures\SignatureLoader;
use CyberShield\Security\Signatures\Signature;

class SignatureLoaderTest extends TestCase
{
    public function test_it_can_load_all_signatures()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $signatures = $loader->loadAll();

        $this->assertIsArray($signatures);
        $this->assertNotEmpty($signatures);
        $this->assertInstanceOf(Signature::class, $signatures[0]);
    }

    public function test_it_loads_sql_signatures_with_correct_data()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $signatures = $loader->loadAll();

        $sqlSig = collect($signatures)->first(fn($sig) => $sig->id === 'SQLI-001');

        $this->assertNotNull($sqlSig);
        $this->assertEquals('Basic SQL Injection', $sqlSig->name);
        $this->assertEquals('critical', $sqlSig->severity);
        $this->assertContains('sqli', $sqlSig->tags);
    }

    public function test_it_can_filter_by_tag()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $xssSignatures = $loader->loadByTag('xss');

        $this->assertNotEmpty($xssSignatures);
        foreach ($xssSignatures as $sig) {
            $this->assertContains('xss', $sig->tags);
        }
    }
}
