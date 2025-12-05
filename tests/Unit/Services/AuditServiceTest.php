<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Services;

use Squareetlabs\LaravelSimplePermissions\Models\AuditLog;
use Squareetlabs\LaravelSimplePermissions\Support\Services\AuditService;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;

class AuditServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable audit logging for tests
        config()->set('simple-permissions.audit.enabled', true);
    }

    /**
     * @test
     */
    public function it_can_log_an_event(): void
    {
        $user = User::factory()->create();
        $service = new AuditService();

        $service->log('test_event', $user, null, ['key' => 'value']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'test_event',
        ]);

        $log = AuditLog::first();
        $this->assertNotNull($log);
    }

    /**
     * @test
     */
    public function it_can_log_an_event_without_user(): void
    {
        $service = new AuditService();

        $service->log('system_event', null);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'system_event',
        ]);
    }
}
