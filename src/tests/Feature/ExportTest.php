<?php

namespace CyberShield\Tests\Feature;

use CyberShield\Tests\TestCase;
use CyberShield\Logging\SecurityLogger;
use Illuminate\Support\Facades\File;

class ExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(storage_path('logs/cybershield'));
        
        SecurityLogger::log('request', ['message' => 'Export test message', 'status' => 200]);
    }

    public function test_it_can_export_csv()
    {
        $response = $this->get(route('cybershield.logs.export.csv', ['channel' => 'request']));

        $response->assertStatus(200);
        $response->assertHeader('Content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=cybershield_logs_' . date('Ymd_His') . '.csv');
        $this->assertStringContainsString('Export test message', $response->streamedContent());
    }

    public function test_it_can_export_json()
    {
        $response = $this->get(route('cybershield.logs.export.json', ['channel' => 'request']));

        $response->assertStatus(200);
        $this->assertStringContainsString('Export test message', $response->streamedContent());
        $this->assertJson($response->streamedContent());
    }
}
