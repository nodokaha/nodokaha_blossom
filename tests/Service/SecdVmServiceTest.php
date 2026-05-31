<?php

namespace App\Tests\Service;

use App\Service\SecdVmService;
use PHPUnit\Framework\TestCase;

final class SecdVmServiceTest extends TestCase
{
    public function testRunsArithmeticAndStoresEnvironment(): void
    {
        $result = (new SecdVmService())->run("LDC 8\nLDC 5\nMUL\nST score\nLD score\nOUT\nSTOP");

        self::assertSame(40, $result['environment']['score']);
        self::assertSame([40], $result['output']);
        self::assertTrue($result['halted']);
        self::assertNull($result['error']);
    }

    public function testRunsSelectionBranch(): void
    {
        $program = "LDC 4\nLDC 2\nGT\nSEL LDC win; OUT; JOIN | LDC lose; OUT; JOIN\nSTOP";

        $result = (new SecdVmService())->run($program);

        self::assertSame(['win'], $result['output']);
        self::assertNull($result['error']);
    }
}
