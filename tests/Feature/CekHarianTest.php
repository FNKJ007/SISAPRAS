<?php

use App\Models\User;
use App\Models\Unit;
use App\Models\Pos;
use App\Models\Peralatan;
use App\Models\CekHarianUnit;
use App\Models\CekHarianAlat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can submit cek harian unit pemadam', function () {
    $user = User::factory()->create(['role' => 'user']);
    $unit = Unit::create([
        'nama' => 'Pancar 01',
        'kategori' => 'pemadam',
        'nomor_lambung' => 'P-01',
        'plat_nomor' => 'D 8518 V',
        'status' => 'aktif',
    ]);
    $pos = Pos::create([
        'nama' => 'Mako Soreang',
        'personil_pemadam' => 5,
        'personil_rescue' => 2,
        'personil_cc' => 1,
        'unit_truck_pancar' => 1,
        'unit_motor_roda3' => 0,
        'unit_motor_roda2' => 0,
        'unit_pompa' => 0,
        'unit_rescue' => 0,
        'unit_water_supply' => 0,
        'unit_lainnya' => 0,
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($user)->post(route('unit-pemadam.cek-harian-unit.store'), [
        'nama_pemeriksa' => 'Petugas Test',
        'jabatan' => 'Danru',
        'unit_id' => $unit->id,
        'pos' => $pos->nama,
        'jenis_bbm' => 'solar',
        'perlengkapan' => [
            'engine_starter' => ['status' => 'baik', 'catatan' => 'OK'],
            'rem_tangan' => ['status' => 'rusak', 'catatan' => 'Bocor'],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cek_harian_units', [
        'user_id' => $user->id,
        'unit_id' => $unit->id,
        'kategori' => 'pemadam',
        'nama_pemeriksa' => 'Petugas Test',
    ]);

    // Check relationship
    $cekUnit = CekHarianUnit::first();
    expect($cekUnit->user->id)->toBe($user->id);
    expect($cekUnit->unit->id)->toBe($unit->id);
});

test('authenticated user can submit cek harian alat rescue', function () {
    $user = User::factory()->create(['role' => 'user']);
    $unit = Unit::create([
        'nama' => 'Rescue 01',
        'kategori' => 'rescue',
        'nomor_lambung' => 'R-01',
        'plat_nomor' => 'D 9933 V',
        'status' => 'aktif',
    ]);
    $alat = Peralatan::create([
        'nama' => 'Chain Saw',
        'kategori' => 'rescue',
        'jumlah_total' => 2,
        'status' => 'baik',
    ]);

    $response = $this->actingAs($user)->post(route('alat-rescue.cek-harian-alat.store'), [
        'nama_pemeriksa' => 'Petugas Rescue',
        'jabatan' => 'Anggota',
        'unit_id' => $unit->id,
        'pos' => 'Soreang',
        'tanggal_pemeriksaan' => now()->toDateString(),
        'alat' => [
            [
                'id' => $alat->id,
                'jumlah_baik' => 2,
                'jumlah_rusak' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cek_harian_alats', [
        'user_id' => $user->id,
        'unit_id' => $unit->id,
        'kategori' => 'rescue',
    ]);

    // Check relationship
    $cekAlat = CekHarianAlat::first();
    expect($cekAlat->user->id)->toBe($user->id);
    expect($cekAlat->unit->id)->toBe($unit->id);
});
