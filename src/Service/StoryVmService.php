<?php

namespace App\Service;

class StoryVmService
{
    private const string SIGNAL_BROADCAST = 'broadcast';
    private const string SIGNAL_INFLUENCE = 'influence';
    private const int DEFAULT_SIGNAL_IMPACT = 1;
    private const int MIN_SIGNAL_IMPACT = -5;
    private const int MAX_SIGNAL_IMPACT = 5;

    public function runProgram(array $program, array $globalStack = [], array $globalEnv = []): array
    {
        $stack = [];
        $env = $globalEnv;
        $dump = [];
        $trace = [];
        $networkSignals = [];
        $pc = 0;
        $programSize = count($program);

        while ($pc < $programSize) {
            $instruction = $program[$pc];
            $opcode = $this->readOpcode($instruction);
            $args = $this->readArguments($instruction);

            switch ($opcode) {
                case 'LDC':
                    $stack[] = $this->readLiteral($args[0] ?? null);
                    break;
                case 'LD':
                    $stack[] = $env[$args[0] ?? ''] ?? null;
                    break;
                case 'ST':
                    if ($args[0] ?? false) {
                        $env[$args[0]] = array_pop($stack);
                    }
                    break;
                case 'LDG':
                    $stack[] = $this->readGlobalStackValue($globalStack, $args[0] ?? null);
                    break;
                case 'ADD':
                case 'SUB':
                case 'MUL':
                case 'DIV':
                    $this->applyArithmeticOpcode($opcode, $stack);
                    break;
                case 'SEL':
                    $cond = array_pop($stack);
                    $then = (int) ($args[0] ?? $pc + 1);
                    $else = (int) ($args[1] ?? $pc + 1);
                    $dump[] = $pc + 1;
                    $pc = ($cond ? $then : $else) - 1;
                    break;
                case 'JOIN':
                    if ($dump !== []) {
                        $pc = ((int) array_pop($dump)) - 1;
                    }
                    break;
                case 'BROADCAST':
                    $networkSignals[] = $this->createBroadcastSignal($args);
                    break;
                case 'INFLUENCE':
                    $networkSignals[] = $this->createInfluenceSignal($args);
                    break;
                case 'STOP':
                    $pc = $programSize;
                    break;
                default:
                    break;
            }

            $trace[] = $this->createTraceEntry($pc, $opcode, $stack, $env, $dump);
            $pc++;
        }

        return [
            'stack' => $stack,
            'env' => $env,
            'dump' => $dump,
            'trace' => $trace,
            'network_signals' => $networkSignals,
        ];
    }

    public function runTileProgram(array $stackData, array $globalStack = []): array
    {
        return $this->runProgram($stackData, $globalStack);
    }

    public function aggregateNetworkSignals(array $results): array
    {
        $broadcasts = [];
        $influences = [];

        foreach ($results as $result) {
            foreach ($result['network_signals'] ?? [] as $signal) {
                if (($signal['type'] ?? null) === self::SIGNAL_BROADCAST) {
                    $broadcasts[] = $signal;
                } elseif (($signal['type'] ?? null) === self::SIGNAL_INFLUENCE) {
                    $influences[] = $signal;
                }
            }
        }

        return [
            'broadcasts' => $broadcasts,
            'influences' => $influences,
        ];
    }

    private function readOpcode(array $instruction): string
    {
        return strtoupper((string) ($instruction['opcode'] ?? ''));
    }

    /**
     * @return list<string>
     */
    private function readArguments(array $instruction): array
    {
        $argText = (string) ($instruction['args'] ?? '');

        if ($argText === '') {
            return [];
        }

        return array_map('trim', explode(',', $argText));
    }

    private function readLiteral(?string $value): mixed
    {
        return is_numeric($value) ? (float) $value : $value;
    }

    private function readGlobalStackValue(array $globalStack, ?string $indexArgument): mixed
    {
        $index = (int) ($indexArgument ?? -1);

        return $index >= 0 && isset($globalStack[$index]) ? $globalStack[$index] : null;
    }

    /**
     * @param list<mixed> $stack
     */
    private function applyArithmeticOpcode(string $opcode, array &$stack): void
    {
        if (count($stack) < 2) {
            return;
        }

        $b = $stack[count($stack) - 1];
        $a = $stack[count($stack) - 2];

        if (!is_numeric($a) || !is_numeric($b)) {
            return;
        }

        if ($opcode === 'DIV' && (float) $b === 0.0) {
            return;
        }

        array_pop($stack);
        array_pop($stack);
        $stack[] = match ($opcode) {
            'ADD' => $a + $b,
            'SUB' => $a - $b,
            'MUL' => $a * $b,
            'DIV' => $a / $b,
        };
    }

    /**
     * @param list<string> $args
     *
     * @return array{type: string, channel: string, impact: int}
     */
    private function createBroadcastSignal(array $args): array
    {
        return [
            'type' => self::SIGNAL_BROADCAST,
            'channel' => (string) ($args[0] ?? 'global'),
            'impact' => $this->readImpact($args[1] ?? null),
        ];
    }

    /**
     * @param list<string> $args
     *
     * @return array{type: string, target: string, impact: int}
     */
    private function createInfluenceSignal(array $args): array
    {
        return [
            'type' => self::SIGNAL_INFLUENCE,
            'target' => mb_strtolower((string) ($args[0] ?? 'all')),
            'impact' => $this->readImpact($args[1] ?? null),
        ];
    }

    private function readImpact(?string $value): int
    {
        $impact = is_numeric($value) ? (int) round((float) $value) : self::DEFAULT_SIGNAL_IMPACT;

        return max(self::MIN_SIGNAL_IMPACT, min(self::MAX_SIGNAL_IMPACT, $impact));
    }

    private function createTraceEntry(int $pc, string $opcode, array $stack, array $env, array $dump): array
    {
        return [
            'pc' => $pc + 1,
            'opcode' => $opcode,
            'stack' => $stack,
            'env' => $env,
            'dump' => $dump,
        ];
    }
}
