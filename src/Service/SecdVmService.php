<?php

namespace App\Service;

final class SecdVmService
{
    private const int DEFAULT_STEP_LIMIT = 160;

    /**
     * @return array{stack: list<mixed>, environment: array<string, mixed>, control: list<array{opcode: string, args: list<string>, source: string}>, dump: list<array<string, mixed>>, trace: list<array<string, mixed>>, output: list<mixed>, halted: bool, error: string|null}
     */
    public function run(string $source, array $environment = [], int $stepLimit = self::DEFAULT_STEP_LIMIT): array
    {
        $stack = [];
        $env = $environment;
        $control = $this->parse($source);
        $dump = [];
        $trace = [];
        $output = [];
        $halted = false;
        $error = null;
        $steps = 0;

        while ($control !== [] && !$halted) {
            if ($steps >= $stepLimit) {
                $error = sprintf('step limit exceeded (%d)', $stepLimit);
                break;
            }

            $instruction = array_shift($control);
            $opcode = $instruction['opcode'];
            $args = $instruction['args'];

            try {
                match ($opcode) {
                    'LDC' => $stack[] = $this->readLiteral(implode(' ', $args)),
                    'LD' => $stack[] = $env[$args[0] ?? ''] ?? null,
                    'ST' => $this->store($stack, $env, $args[0] ?? ''),
                    'ADD', 'SUB', 'MUL', 'DIV' => $this->arithmetic($stack, $opcode),
                    'EQ', 'GT', 'LT' => $this->compare($stack, $opcode),
                    'CONS' => $this->cons($stack),
                    'CAR' => $this->car($stack),
                    'CDR' => $this->cdr($stack),
                    'ATOM' => $stack[] = !is_array(array_pop($stack)),
                    'SEL' => $this->select($stack, $control, $dump, implode(' ', $args)),
                    'JOIN' => $this->join($control, $dump),
                    'OUT' => $output[] = $stack[count($stack) - 1] ?? null,
                    'STOP' => $halted = true,
                    default => throw new \InvalidArgumentException(sprintf('unknown opcode: %s', $opcode)),
                };
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
                break;
            }

            $trace[] = [
                'step' => $steps + 1,
                'instruction' => $instruction['source'],
                'stack' => $stack,
                'environment' => $env,
                'control_size' => count($control),
                'dump_size' => count($dump),
                'output' => $output,
            ];
            ++$steps;
        }

        return [
            'stack' => $stack,
            'environment' => $env,
            'control' => $control,
            'dump' => $dump,
            'trace' => $trace,
            'output' => $output,
            'halted' => $halted || $control === [],
            'error' => $error,
        ];
    }

    /**
     * @return list<array{opcode: string, args: list<string>, source: string}>
     */
    public function parse(string $source): array
    {
        $program = [];
        foreach (preg_split('/\R/', $source) ?: [] as $line) {
            $line = trim(preg_replace('/#.*/', '', $line) ?? '');
            if ($line === '') {
                continue;
            }

            $sourceLines = str_starts_with(strtoupper($line), 'SEL ') ? [$line] : array_filter(array_map('trim', explode(';', $line)));
            foreach ($sourceLines as $sourceLine) {
                [$opcode, $argumentText] = array_pad(preg_split('/\s+/', $sourceLine, 2) ?: [], 2, '');
                $args = $argumentText === '' ? [] : array_values(array_filter(str_getcsv($argumentText, ' ', '"', '\\'), static fn(string $arg): bool => $arg !== ''));
                $program[] = [
                    'opcode' => strtoupper($opcode),
                    'args' => $args,
                    'source' => $sourceLine,
                ];
            }
        }

        return $program;
    }

    /**
     * @param list<mixed> $stack
     * @param array<string, mixed> $env
     */
    private function store(array &$stack, array &$env, string $name): void
    {
        if ($name === '') {
            throw new \InvalidArgumentException('ST requires a variable name');
        }

        $env[$name] = array_pop($stack);
    }

    /** @param list<mixed> $stack */
    private function arithmetic(array &$stack, string $opcode): void
    {
        [$a, $b] = $this->popPair($stack);
        if (!is_numeric($a) || !is_numeric($b)) {
            throw new \InvalidArgumentException($opcode.' requires two numeric values');
        }
        if ($opcode === 'DIV' && (float) $b === 0.0) {
            throw new \InvalidArgumentException('division by zero');
        }

        $stack[] = match ($opcode) {
            'ADD' => $a + $b,
            'SUB' => $a - $b,
            'MUL' => $a * $b,
            'DIV' => $a / $b,
        };
    }

    /** @param list<mixed> $stack */
    private function compare(array &$stack, string $opcode): void
    {
        [$a, $b] = $this->popPair($stack);
        $stack[] = match ($opcode) {
            'EQ' => $a == $b,
            'GT' => $a > $b,
            'LT' => $a < $b,
        };
    }

    /** @param list<mixed> $stack */
    private function cons(array &$stack): void
    {
        [$head, $tail] = $this->popPair($stack);
        $stack[] = array_merge([$head], is_array($tail) ? $tail : [$tail]);
    }

    /** @param list<mixed> $stack */
    private function car(array &$stack): void
    {
        $value = array_pop($stack);
        $stack[] = is_array($value) ? ($value[0] ?? null) : null;
    }

    /** @param list<mixed> $stack */
    private function cdr(array &$stack): void
    {
        $value = array_pop($stack);
        $stack[] = is_array($value) ? array_slice($value, 1) : [];
    }

    /**
     * @param list<mixed> $stack
     * @param list<array{opcode: string, args: list<string>, source: string}> $control
     * @param list<array<string, mixed>> $dump
     */
    private function select(array &$stack, array &$control, array &$dump, string $branches): void
    {
        $parts = array_map('trim', explode('|', $branches, 2));
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('SEL requires two branches separated by |');
        }

        $condition = (bool) array_pop($stack);
        $selectedControl = $this->parse($condition ? $parts[0] : $parts[1]);
        $remainder = $this->extractBranchRemainder($selectedControl);
        $dump[] = ['control' => $control];
        $control = $remainder === [] ? array_merge($selectedControl, $control) : $selectedControl;
    }

    /**
     * @param list<array{opcode: string, args: list<string>, source: string}> $control
     * @return list<array{opcode: string, args: list<string>, source: string}>
     */
    private function extractBranchRemainder(array &$control): array
    {
        $remainder = [];
        foreach ($control as $index => $instruction) {
            if ($instruction['opcode'] === 'JOIN') {
                $remainder = array_slice($control, $index + 1);
                $control = array_slice($control, 0, $index + 1);
                break;
            }
        }

        return $remainder;
    }

    /**
     * @param list<array{opcode: string, args: list<string>, source: string}> $control
     * @param list<array<string, mixed>> $dump
     */
    private function join(array &$control, array &$dump): void
    {
        $frame = array_pop($dump);
        if (is_array($frame['control'] ?? null)) {
            $control = $frame['control'];
        }
    }

    /**
     * @param list<mixed> $stack
     * @return array{mixed, mixed}
     */
    private function popPair(array &$stack): array
    {
        if (count($stack) < 2) {
            throw new \InvalidArgumentException('stack underflow');
        }

        $b = array_pop($stack);
        $a = array_pop($stack);

        return [$a, $b];
    }

    private function readLiteral(string $value): mixed
    {
        $value = trim($value);
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        if ($value === 'null') {
            return null;
        }
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $value;
    }
}
