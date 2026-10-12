<?php

namespace App\Console\Commands;

use App\Models\CaseModel;
use App\Services\ScenarioGraphValidator;
use Illuminate\Console\Command;

class ValidateCaseGraph extends Command
{
    protected $signature = 'bsds:validate-case {code? : Kode kasus, kosongkan untuk semua kasus}';

    protected $description = 'Validasi graf percabangan skenario (jalan buntu, yatim, siklus)';

    public function handle(ScenarioGraphValidator $validator): int
    {
        $cases = CaseModel::query()
            ->when($this->argument('code'), fn ($q, $code) => $q->where('code', $code))
            ->get();

        if ($cases->isEmpty()) {
            $this->error('Kasus tidak ditemukan.');

            return self::FAILURE;
        }

        $failed = false;

        foreach ($cases as $case) {
            $errors = $validator->validate($case);

            if ($errors === []) {
                $this->info("{$case->code}: valid");

                continue;
            }

            $failed = true;
            $this->error("{$case->code}: ".count($errors).' masalah');
            foreach ($errors as $error) {
                $this->line(" - {$error}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
