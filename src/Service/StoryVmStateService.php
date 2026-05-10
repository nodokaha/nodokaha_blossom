<?php

namespace App\Service;

class StoryVmStateService
{
    private const STATE_FILE = __DIR__.'/../../var/story_vm_state.json';

    public function loadState(): array
    {
        if (!is_file(self::STATE_FILE)) {
            return ['program' => [], 'last_insert_date' => [], 'run_result' => null, 'world' => $this->defaultWorld()];
        }

        $data = json_decode((string) file_get_contents(self::STATE_FILE), true);

        if (!is_array($data)) {
            return ['program' => [], 'last_insert_date' => [], 'run_result' => null, 'world' => $this->defaultWorld()];
        }

        $data['world'] = array_replace($this->defaultWorld(), is_array($data['world'] ?? null) ? $data['world'] : []);

        return $data;
    }

    public function saveState(array $state): void
    {
        if (!is_dir(dirname(self::STATE_FILE))) {
            mkdir(dirname(self::STATE_FILE), 0777, true);
        }
        file_put_contents(self::STATE_FILE, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }


    private function defaultWorld(): array
    {
        return [
            'day' => 1,
            'chapter' => 'CHAPTER 1: 花畑の起動',
            'objective' => '光と水の変数を整えて、開花率を20%以上にする',
            'biome' => [
                'weather' => 'mist',
                'bloom_rate' => 0,
                'energy' => 5,
            ],
            'npcs' => [
                'caretaker_ai' => '待機中',
            ],
            'chronicle' => [],
        ];
    }
}
