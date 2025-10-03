<?php

namespace Rector\Tests\PHPUnit\WithConsecutiveToCallbackRector\Fixture;

class DataProvider
{
    public function getData($id)
    {
        // Implementation
    }
}

class WithReturnValuesTest extends \PHPUnit\Framework\TestCase
{
    public function testWithReturnValues()
    {
        $dataProvider = $this->createMock(DataProvider::class);
        $dataProvider->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive(
                [1],
                [2]
            );
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\WithConsecutiveToCallbackRector\Fixture;

class DataProvider
{
    public function getData($id)
    {
        // Implementation
    }
}

class WithReturnValuesTest extends \PHPUnit\Framework\TestCase
{
    public function testWithReturnValues()
    {
        $dataProvider = $this->createMock(DataProvider::class);
        $dataProvider->expects($this->exactly(2))->method('getData')->willReturnCallback(function ($parameters) {
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
}

?>