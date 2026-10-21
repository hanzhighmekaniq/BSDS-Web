<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseModel extends Model
{
    use HasFactory;

    protected $table = 'cases';

    public const CATEGORIES = [
        'pelanggan_tidak_puas' => 'Penanganan Pelanggan Tidak Puas',
        'prioritas_pelanggan' => 'Penentuan Prioritas Pelanggan',
        'kesalahan_komunikasi' => 'Kesalahan Komunikasi',
        'penanganan_keluhan' => 'Penanganan Keluhan',
        'pemenuhan_permintaan' => 'Pemenuhan Permintaan',
        'kesalahan_pelayanan' => 'Kesalahan Pelayanan',
    ];

    protected $fillable = [
        'code',
        'title',
        'description',
        'level',
        'category',
        'role_title',
        'briefing_text',
        'estimated_time',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'estimated_time' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(Scenario::class, 'case_id');
    }

    public function simulationSessions(): HasMany
    {
        return $this->hasMany(SimulationSession::class, 'case_id');
    }

    public function initialScenario(): ?Scenario
    {
        return $this->scenarios()->where('is_start', true)->first()
            ?? $this->scenarios()->where('step_code', 'S1')->first()
            ?? $this->scenarios()->orderBy('id')->first();
    }
}
