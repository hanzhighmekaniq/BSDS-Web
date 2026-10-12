<?php

namespace App\Services;

use App\Models\CaseModel;

/**
 * Memvalidasi graf percabangan sebuah kasus agar tidak ada jalan buntu,
 * skenario yatim, atau siklus tak berujung.
 */
class ScenarioGraphValidator
{
    /**
     * @return array<int,string> daftar masalah (kosong = valid)
     */
    public function validate(CaseModel $case): array
    {
        $scenarios = $case->scenarios()->with('options')->get()->keyBy('id');
        $errors = [];

        $starts = $scenarios->where('is_start', true);
        if ($starts->count() !== 1) {
            $errors[] = "Harus ada tepat 1 skenario awal (is_start), ditemukan {$starts->count()}.";
        }

        if ($scenarios->where('is_ending', true)->isEmpty()) {
            $errors[] = 'Tidak ada skenario ending (is_ending).';
        }

        foreach ($scenarios as $scenario) {
            if ($scenario->is_ending) {
                if ($scenario->options->isNotEmpty()) {
                    $errors[] = "Ending {$scenario->step_code} tidak boleh punya opsi.";
                }

                continue;
            }

            if ($scenario->options->isEmpty()) {
                $errors[] = "Skenario {$scenario->step_code} tidak punya opsi (jalan buntu).";
            }

            foreach ($scenario->options as $option) {
                if (! $option->next_scenario_id) {
                    $errors[] = "Opsi {$scenario->step_code}-{$option->label} tidak punya skenario tujuan.";
                } elseif (! $scenarios->has($option->next_scenario_id)) {
                    $errors[] = "Opsi {$scenario->step_code}-{$option->label} menuju skenario di luar kasus ini.";
                }
            }
        }

        if ($starts->count() === 1) {
            $start = $starts->first();
            $reachable = $this->reachable($start->id, $scenarios);

            foreach ($scenarios->keys()->diff($reachable) as $orphanId) {
                $errors[] = "Skenario {$scenarios[$orphanId]->step_code} tidak dapat dicapai dari skenario awal (yatim).";
            }

            if ($this->hasCycle($start->id, $scenarios)) {
                $errors[] = 'Terdapat siklus pada alur skenario (dapat berputar tanpa berujung).';
            }
        }

        return $errors;
    }

    protected function reachable(int $startId, $scenarios): array
    {
        $seen = [];
        $stack = [$startId];

        while ($stack) {
            $id = array_pop($stack);
            if (isset($seen[$id]) || ! $scenarios->has($id)) {
                continue;
            }
            $seen[$id] = true;

            foreach ($scenarios[$id]->options as $option) {
                if ($option->next_scenario_id) {
                    $stack[] = $option->next_scenario_id;
                }
            }
        }

        return array_keys($seen);
    }

    protected function hasCycle(int $startId, $scenarios): bool
    {
        $state = [];

        $visit = function (int $id) use (&$visit, &$state, $scenarios): bool {
            if (! $scenarios->has($id)) {
                return false;
            }
            if (($state[$id] ?? 0) === 1) {
                return true;
            }
            if (($state[$id] ?? 0) === 2) {
                return false;
            }

            $state[$id] = 1;
            foreach ($scenarios[$id]->options as $option) {
                if ($option->next_scenario_id && $visit($option->next_scenario_id)) {
                    return true;
                }
            }
            $state[$id] = 2;

            return false;
        };

        return $visit($startId);
    }
}
