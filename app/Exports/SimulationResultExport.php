<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class SimulationResultExport implements FromCollection
{
    public function collection(): Collection
    {
        //
    }
}
