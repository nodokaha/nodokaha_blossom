<?php

namespace App\Service;

class StoryVmService
{
    public function runProgram(array $program): array
    {
        $stack = [];
        $env = [];
        $dump = [];
        $pc = 0;
        $trace = [];

        while ($pc < count($program)) {
            $ins = $program[$pc];
            $opcode = strtoupper((string) ($ins['opcode'] ?? ''));
            $argText = (string) ($ins['args'] ?? '');
            $args = array_map('trim', $argText === '' ? [] : explode(',', $argText));

            switch ($opcode) {
                case 'LDC':
                    $stack[] = is_numeric($args[0] ?? null) ? (float) $args[0] : ($args[0] ?? null);
                    break;
                case 'LD':
                    $stack[] = $env[$args[0] ?? ''] ?? null;
                    break;
                case 'ST':
                    if ($args[0] ?? false) {
                        $env[$args[0]] = array_pop($stack);
                    }
                    break;
                case 'ADD':
                case 'SUB':
                case 'MUL':
                case 'DIV':
                    if (count($stack) < 2) {
                        break;
                    }

                    $b = $stack[count($stack) - 1];
                    $a = $stack[count($stack) - 2];
                    if (!is_numeric($a) || !is_numeric($b)) {
                        break;
                    }
                    if ($opcode === 'DIV' && (float) $b === 0.0) {
                        break;
                    }

                    array_pop($stack);
                    array_pop($stack);
                    if ($opcode === 'ADD') {$stack[] = $a + $b;}
                    if ($opcode === 'SUB') {$stack[] = $a - $b;}
                    if ($opcode === 'MUL') {$stack[] = $a * $b;}
                    if ($opcode === 'DIV') {$stack[] = $a / $b;}
                    break;
                case 'SEL':
                    $cond = array_pop($stack);
                    $then = (int) ($args[0] ?? $pc + 1);
                    $else = (int) ($args[1] ?? $pc + 1);
                    $dump[] = $pc + 1;
                    $pc = ($cond ? $then : $else) - 1;
                    break;
                case 'JOIN':
                    if ($dump === []) {
                        break;
                    }
                    $pc = ((int) array_pop($dump)) - 1;
                    break;
                case 'STOP':
                    $pc = count($program);
                    break;
                default:
                    break;
            }

            $trace[] = ['pc' => $pc + 1, 'opcode' => $opcode, 'stack' => $stack, 'env' => $env, 'dump' => $dump];
            $pc++;
        }

        return ['stack' => $stack, 'env' => $env, 'dump' => $dump, 'trace' => $trace];
    }
}