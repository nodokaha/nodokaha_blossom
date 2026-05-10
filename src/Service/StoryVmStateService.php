<?php

namespace App\Service;

class StoryVmStateService
{
    private const STATE_FILE = __DIR__.'/../../var/story_vm_state.json';

    public function loadState(): array
    {
        if (!is_file(self::STATE_FILE)) {
            return ['program' => [], 'last_insert_date' => [], 'run_result' => null];
        }

        $data = json_decode((string) file_get_contents(self::STATE_FILE), true);

        return is_array($data) ? $data : ['program' => [], 'last_insert_date' => [], 'run_result' => null];
    }

    public function saveState(array $state): void
    {
        if (!is_dir(dirname(self::STATE_FILE))) {
            mkdir(dirname(self::STATE_FILE), 0777, true);
        }
        file_put_contents(self::STATE_FILE, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}