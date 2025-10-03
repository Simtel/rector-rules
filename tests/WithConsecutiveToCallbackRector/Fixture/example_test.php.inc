<?php

namespace Rector\Tests\PHPUnit\WithConsecutiveToCallbackRector\Fixture;

class UserService
{
    public function getUserById($id)
    {
        // Implementation
    }
    
    public function getUserByEmail($email)
    {
        // Implementation
    }
}

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testUserService()
    {
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(2))
            ->method('getUserById')
            ->withConsecutive(
                [1],
                [2]
            );
    }
    
    public function testUserEmail()
    {
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(2))
            ->method('getUserByEmail')
            ->withConsecutive(
                ['user1@example.com'],
                ['user2@example.com']
            );
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\WithConsecutiveToCallbackRector\Fixture;

class UserService
{
    public function getUserById($id)
    {
        // Implementation
    }
    
    public function getUserByEmail($email)
    {
        // Implementation
    }
}

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testUserService()
    {
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(2))->method('getUserById')->willReturnCallback(function ($parameters) {
            static $callCount = 0;
            $callCount++;
            if ($callCount === 1) {
                $this->assertSame([1], $parameters);
            }
            if ($callCount === 2) {
                $this->assertSame([2], $parameters);
            }
        });
    }
    
    public function testUserEmail()
    {
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(2))->method('getUserByEmail')->willReturnCallback(function ($parameters) {
            static $callCount = 0;
            $callCount++;
            if ($callCount === 1) {
                $this->assertSame(['user1@example.com'], $parameters);
            }
            if ($callCount === 2) {
                $this->assertSame(['user2@example.com'], $parameters);
            }
        });
    }
}

?>