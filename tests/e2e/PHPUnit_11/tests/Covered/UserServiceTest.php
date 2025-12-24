<?php

namespace Infection\E2ETests\PHPUnit_11\Tests\Covered;

use Infection\E2ETests\PHPUnit_11\Covered\LoggerTrait;
use Infection\E2ETests\PHPUnit_11\Covered\UserService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(LoggerTrait::class)]
#[CoversClass(UserService::class)]
class UserServiceTest extends TestCase
{
    private UserService $service;

    protected function setUp(): void
    {
        $this->service = new UserService();
    }

    public function test_add_user_successfully(): void
    {
        $result = $this->service->addUser('John Doe', 'john@example.com');

        $this->assertTrue($result);
        $this->assertSame(1, $this->service->getUserCount());
        $this->assertTrue($this->service->hasLogs());
    }

    public function test_add_user_with_empty_name_fails(): void
    {
        $result = $this->service->addUser('', 'john@example.com');

        $this->assertFalse($result);
        $this->assertSame(0, $this->service->getUserCount());

        $logs = $this->service->getLogs();
        $this->assertCount(1, $logs);
        $this->assertSame('Failed to add user: empty name or email', $logs[0]);
    }

    public function test_add_user_with_empty_email_fails(): void
    {
        $result = $this->service->addUser('John Doe', '');

        $this->assertFalse($result);
        $this->assertSame(0, $this->service->getUserCount());

        $logs = $this->service->getLogs();
        $this->assertCount(1, $logs);
        $this->assertSame('Failed to add user: empty name or email', $logs[0]);
    }

    public function test_add_duplicate_user_fails(): void
    {
        $this->service->addUser('John Doe', 'john@example.com');
        $this->service->clearLogs();

        $result = $this->service->addUser('Jane Doe', 'john@example.com');

        $this->assertFalse($result);
        $this->assertSame(1, $this->service->getUserCount());

        $logs = $this->service->getLogs();
        $this->assertCount(1, $logs);
        $this->assertSame('Failed to add user: email john@example.com already exists', $logs[0]);
    }

    public function test_remove_user_successfully(): void
    {
        $this->service->addUser('John Doe', 'john@example.com');
        $this->service->clearLogs();

        $result = $this->service->removeUser('john@example.com');

        $this->assertTrue($result);
        $this->assertSame(0, $this->service->getUserCount());

        $logs = $this->service->getLogs();
        $this->assertCount(1, $logs);
        $this->assertSame('User john@example.com removed successfully', $logs[0]);
    }

    public function test_remove_non_existent_user_fails(): void
    {
        $result = $this->service->removeUser('john@example.com');

        $this->assertFalse($result);

        $logs = $this->service->getLogs();
        $this->assertCount(1, $logs);
        $this->assertSame('Failed to remove user: email john@example.com not found', $logs[0]);
    }

    public function test_get_user_returns_user_data(): void
    {
        $this->service->addUser('John Doe', 'john@example.com');
        $user = $this->service->getUser('john@example.com');

        $this->assertIsArray($user);
        $this->assertSame('John Doe', $user['name']);
        $this->assertSame('john@example.com', $user['email']);
    }

    public function test_get_user_returns_null_for_non_existent_user(): void
    {
        $user = $this->service->getUser('john@example.com');

        $this->assertNull($user);
    }

    public function test_user_exists(): void
    {
        $this->assertFalse($this->service->userExists('john@example.com'));

        $this->service->addUser('John Doe', 'john@example.com');

        $this->assertTrue($this->service->userExists('john@example.com'));
    }

    public function test_logger_trait_methods(): void
    {
        $this->assertFalse($this->service->hasLogs());
        $this->assertEmpty($this->service->getLogs());

        $this->service->addUser('John Doe', 'john@example.com');

        $this->assertTrue($this->service->hasLogs());
        $this->assertNotEmpty($this->service->getLogs());

        $this->service->clearLogs();

        $this->assertFalse($this->service->hasLogs());
        $this->assertEmpty($this->service->getLogs());
    }

    public function test_log_method_is_public(): void
    {
        // Test that log() method can be called from outside the class
        // If it's protected, this will cause a fatal error
        $reflection = new \ReflectionMethod(UserService::class, 'log');
        $this->assertTrue($reflection->isPublic(), 'log() method must be public');

        // Also test we can actually call it
        $this->service->log('Direct log call');
        $logs = $this->service->getLogs();
        $this->assertContains('Direct log call', $logs);
    }
}
