<?php

declare(strict_types=1);

namespace Simtel\RectorRules\Tests\RenameFindAndGetMethodCallRector;

use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameFindAndGetMethodCallRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function testSkipScenariosWhereChangesAreNotApplied(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/skip_rename_scenarios.php.inc');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
