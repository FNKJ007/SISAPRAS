<?php

namespace App\Traits;

use App\Models\Regu;
use App\Models\User;

trait HandlesOfficialsData
{
    /**
     * Menyiapkan data default dan opsi Danru serta Kabid untuk form pengecekan.
     */
    protected function getOfficialsData(?string $targetBidang = null): array
    {
        $currentUser = auth()->user();

        // 1. Ambil daftar pegawai / pejabat untuk Danru & Kabid (Cached 5 min)
        $allPegawai = \App\Services\CacheService::rememberList('officials_pegawai', function () {
            return User::orderBy('name', 'asc')->get(['id', 'name', 'nip', 'jabatan', 'bidang', 'pos', 'regu']);
        });
        $allReguList = \App\Services\CacheService::rememberList('officials_regu', function () {
            return Regu::all(['id', 'nama', 'pos', 'bidang', 'danru', 'nip_danru']);
        });

        $danruUsers = $allPegawai->filter(function ($u) {
            $j = strtolower($u->jabatan ?? '');
            return str_contains($j, 'danru') || str_contains($j, 'komandan') || str_contains($j, 'kasi') || str_contains($j, 'seksi');
        })->values();

        $kabidUsers = $allPegawai->filter(function ($u) {
            $j = strtolower($u->jabatan ?? '');
            return str_contains($j, 'kabid') || str_contains($j, 'kepala bidang') || str_contains($j, 'kadis') || str_contains($j, 'sekretaris');
        })->values();

        // 2. Tentukan Default Danru sesuai Pos & Regu pengguna
        $defaultDanruName = '';
        if ($currentUser && $currentUser->pos && $currentUser->regu) {
            $userPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->pos));
            $userReguClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->regu));

            $matchedRegu = $allReguList->first(function ($r) use ($userPosClean, $userReguClean) {
                $rPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $r->pos ?? ''));
                $rReguClean = strtolower(preg_replace('/[^a-z0-9]/', '', $r->nama ?? ''));
                return (str_contains($rPosClean, $userPosClean) || str_contains($userPosClean, $rPosClean)) && $rReguClean === $userReguClean;
            });

            if ($matchedRegu && !empty($matchedRegu->danru)) {
                $defaultDanruName = $matchedRegu->danru;
            }
        }
        if (empty($defaultDanruName) && $danruUsers->isNotEmpty()) {
            $defaultDanruName = $danruUsers->first()->name ?? '';
        }

        // 3. Tentukan Default Kabid sesuai Bidang pengguna / target bidang
        $defaultKabidName = '';
        $searchBidang = strtolower($targetBidang ?: ($currentUser->bidang ?? ''));
        if (!empty($searchBidang)) {
            $matchedKabid = $kabidUsers->first(function ($u) use ($searchBidang) {
                $uBidangLower = strtolower($u->bidang ?? '');
                $uJabatanLower = strtolower($u->jabatan ?? '');
                return ($uBidangLower && (str_contains($searchBidang, $uBidangLower) || str_contains($uBidangLower, $searchBidang))) ||
                       ($uJabatanLower && str_contains($uJabatanLower, $searchBidang));
            });
            if ($matchedKabid && !empty($matchedKabid->name)) {
                $defaultKabidName = $matchedKabid->name;
            }
        }
        if (empty($defaultKabidName) && $kabidUsers->isNotEmpty()) {
            $defaultKabidName = $kabidUsers->first()->name ?? '';
        }

        // 4. Susun daftar opsi Danru untuk autocomplete
        $danruOptions = collect();
        foreach ($danruUsers as $u) {
            if (!empty($u->name)) {
                $danruOptions->push([
                    'name'    => $u->name,
                    'jabatan' => $u->jabatan ?? 'Danru',
                    'pos'     => $u->pos ?? '',
                    'bidang'  => $u->bidang ?? '',
                ]);
            }
        }
        foreach ($allReguList as $r) {
            if (!empty($r->danru)) {
                $danruOptions->push([
                    'name'    => $r->danru,
                    'jabatan' => 'Danru ' . ($r->nama ?? ''),
                    'pos'     => $r->pos ?? '',
                    'bidang'  => $r->bidang ?? '',
                ]);
            }
        }
        $danruOptions = $danruOptions->unique('name')->sortBy('name')->values();

        // 5. Susun daftar opsi Kabid untuk autocomplete
        $kabidOptions = $kabidUsers
            ->filter(fn($u) => !empty($u->name))
            ->map(fn($u) => [
                'name'    => $u->name,
                'jabatan' => $u->jabatan ?? 'Kepala Bidang',
                'pos'     => $u->pos ?? '',
                'bidang'  => $u->bidang ?? '',
            ])
            ->unique('name')
            ->sortBy('name')
            ->values();

        return [
            'defaultDanruName' => $defaultDanruName,
            'defaultKabidName' => $defaultKabidName,
            'danruOptions'     => $danruOptions,
            'kabidOptions'     => $kabidOptions,
            'allReguList'      => $allReguList,
            'danruUsers'       => $danruUsers,
            'kabidUsers'       => $kabidUsers,
        ];
    }
}

