<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Simtel\RectorRules\Rector\PHPUnit\WithConsecutiveToCallbackRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(WithConsecutiveToCallbackRector::class);
};
