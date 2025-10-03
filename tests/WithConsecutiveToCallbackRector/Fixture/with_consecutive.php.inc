<?php

namespace Rector\Tests\PHPUnit\WithConsecutiveToCallbackRector\Fixture;

class SomeClass
{
    public function someMethod($param)
    {
        // Method implementation
    }
}

class WithConsecutiveFixture
{
    public function test()
    {
        $mock = $this->createMock(SomeClass::class);
        $mock->expects($this->exactly(2))
            ->method('someMethod')
            ->withConsecutive(
                ['first'],
                ['second']
            );
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\WithConsecutiveToCallbackRector\Fixture;

class SomeClass
{
    public function someMethod($param)
    {
        // Method implementation
    }
}

class WithConsecutiveFixture
{
    public function test()
    {
        $mock = $this->createMock(SomeClass::class);
        $mock->expects($this->exactly(2))->method('someMethod')->willReturnCallback(function ($parameters) {
            static $callCount = 0;
            $callCount++;
            if ($callCount === 1) {
                $this->assertSame(['first'], $parameters);
            }
            if ($callCount === 2) {
                $this->assertSame(['second'], $parameters);
            }
        });
    }
}

?>