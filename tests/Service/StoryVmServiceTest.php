<?php

namespace App\Tests\Service;

use App\Service\StoryVmService;
use PHPUnit\Framework\TestCase;

class StoryVmServiceTest extends TestCase
{
    private StoryVmService $service;

    protected function setUp(): void
    {
        $this->service = new StoryVmService();
    }

    public function testRunProgramWithEmptyProgram(): void
    {
        $result = $this->service->runProgram([]);

        $this->assertEquals(['stack' => [], 'env' => [], 'dump' => [], 'trace' => []], $result);
    }

    public function testRunProgramWithLdc(): void
    {
        $program = [['opcode' => 'LDC', 'args' => '42']];

        $result = $this->service->runProgram($program);

        $this->assertEquals([42.0], $result['stack']);
        $this->assertEquals([], $result['env']);
        $this->assertEquals([], $result['dump']);
        $this->assertCount(1, $result['trace']);
    }

    public function testRunProgramWithAdd(): void
    {
        $program = [
            ['opcode' => 'LDC', 'args' => '10'],
            ['opcode' => 'LDC', 'args' => '20'],
            ['opcode' => 'ADD', 'args' => ''],
        ];

        $result = $this->service->runProgram($program);

        $this->assertEquals([30.0], $result['stack']);
    }

    public function testRunProgramWithLdAndSt(): void
    {
        $program = [
            ['opcode' => 'LDC', 'args' => '100'],
            ['opcode' => 'ST', 'args' => 'x'],
            ['opcode' => 'LD', 'args' => 'x'],
        ];

        $result = $this->service->runProgram($program);

        $this->assertEquals([100.0], $result['stack']);
        $this->assertEquals(['x' => 100.0], $result['env']);
    }

    public function testRunProgramWithStop(): void
    {
        $program = [
            ['opcode' => 'LDC', 'args' => '1'],
            ['opcode' => 'STOP', 'args' => ''],
            ['opcode' => 'LDC', 'args' => '2'], // This should not execute
        ];

        $result = $this->service->runProgram($program);

        $this->assertEquals([1.0], $result['stack']);
        $this->assertCount(2, $result['trace']); // Only first two instructions
    }
}