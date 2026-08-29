<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Regu;

class RestorePegawaiReguSeeder extends Seeder
{
    /**
     * Run the database seeds to restore users (pejabat & danru) and regus.
     */
    public function run(): void
    {
        $backupDir = database_path('backups');
        
        $usersFile = "{$backupDir}/users_backup_latest.json";
        $regusFile = "{$backupDir}/regus_backup_latest.json";

        Schema::disableForeignKeyConstraints();

        $danruPosMap = [];
        if (file_exists($regusFile)) {
            $regusData = json_decode(file_get_contents($regusFile), true);
            echo "Restoring " . count($regusData) . " regus...\n";
            
            Regu::truncate();
            foreach ($regusData as $reguData) {
                Regu::create($reguData);
                $dName = trim($reguData['danru'] ?? '');
                if ($dName) {
                    $danruPosMap[$dName] = [
                        'pos'  => $reguData['pos'],
                        'regu' => $reguData['nama'],
                    ];
                }
            }
            echo "Regus restored successfully!\n";
        }

        if (file_exists($usersFile)) {
            $usersData = json_decode(file_get_contents($usersFile), true);
            
            $finalUsers = [];

            // Admin default
            $finalUsers[] = [
                'name'       => 'Asep Deri Hermawan',
                'nip'        => '233040022',
                'email'      => '233040022@disdamkar.go.id',
                'password'   => \Illuminate\Support\Facades\Hash::make('27Agustus'),
                'role'       => 'admin',
                'jabatan'    => 'Anggota / Petugas',
                'bidang'     => 'Pemadam',
                'pos'        => 'Soreang (MAKO)',
                'regu'       => 'Regu 1',
                'status'     => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ($usersData as $u) {
                $j = strtolower(trim($u['jabatan'] ?? ''));
                $isPejabat = preg_match('/(kepala|kabid|kasi|sekretaris|kadis|kasubag|subbag|sub\s*bagian)/i', $j);
                $isDanru = preg_match('/(danru|komandan)/i', $j);

                if (!$isPejabat && !$isDanru) {
                    continue;
                }

                $name = trim($u['name']);
                $nip = trim($u['nip']);
                $jabatan = trim($u['jabatan']);

                $exists = collect($finalUsers)->first(function($it) use ($nip, $name) {
                    return ($nip && $it['nip'] === $nip) || $it['name'] === $name;
                });
                if ($exists) {
                    continue;
                }

                $jabatanClean = preg_replace('/\s+/', ' ', $jabatan);
                if (strtolower($jabatanClean) === 'kepala bidang pemadaman' || strtolower($jabatanClean) === 'kepala  bidang pemadaman') {
                    $jabatanClean = 'Kepala Bidang Pemadaman';
                } elseif (strtolower($jabatanClean) === 'kabid pencegahan kebakaran' || strtolower($jabatanClean) === 'kabid pencegahan  kebakaran') {
                    $jabatanClean = 'Kepala Bidang Pencegahan Kebakaran';
                } elseif (strtolower($jabatanClean) === 'kepala seksi komunikasi dan informasi' || strtolower($jabatanClean) === 'kepala seksi  komunikasi dan informasi') {
                    $jabatanClean = 'Kepala Seksi Komunikasi Dan Informasi';
                }

                $bidang = trim($u['bidang'] ?? '');
                if (strtolower($bidang) === 'spi' || str_contains(strtolower($bidang), 'sarana') || str_contains(strtolower($bidang), 'informasi')) {
                    $bidangClean = 'Sarana Dan Informasi';
                } elseif (str_contains(strtolower($bidang), 'command')) {
                    $bidangClean = 'Command Center';
                } elseif (str_contains(strtolower($bidang), 'pencegahan')) {
                    $bidangClean = 'Pencegahan';
                } elseif (str_contains(strtolower($bidang), 'penyelamatan') || str_contains(strtolower($bidang), 'rescue')) {
                    $bidangClean = 'Rescue';
                } elseif (str_contains(strtolower($bidang), 'pemadam')) {
                    $bidangClean = 'Pemadam';
                } else {
                    $bidangClean = ucwords(strtolower($bidang ?: 'Sekretariat'));
                }

                $posClean = trim($u['pos'] ?? 'Soreang (MAKO)');
                $reguClean = trim($u['regu'] ?? '');

                if ($isDanru && isset($danruPosMap[$name])) {
                    $posClean = $danruPosMap[$name]['pos'];
                    $reguClean = $danruPosMap[$name]['regu'];
                }

                $cleanNipDigits = preg_replace('/[^0-9]/', '', $nip);
                $email = !empty($u['email']) ? $u['email'] : ($cleanNipDigits ? "{$cleanNipDigits}@disdamkar.go.id" : 'pegawai_' . uniqid() . '@disdamkar.go.id');
                while (collect($finalUsers)->contains('email', $email)) {
                    $email = 'pegawai_' . uniqid() . '@disdamkar.go.id';
                }

                $password = !empty($u['password']) ? $u['password'] : '$2y$12$NlmjDskqRzL5/pU3a5kM3.a4c4K/hF9gUu1QO7zH2Y.9S9Z9e0yKy';
                $role = $isPejabat ? 'pejabat' : 'user';

                $finalUsers[] = [
                    'name'       => $name,
                    'nip'        => $nip,
                    'email'      => $email,
                    'password'   => $password,
                    'role'       => $role,
                    'jabatan'    => $jabatanClean,
                    'bidang'     => $bidangClean,
                    'pos'        => $posClean,
                    'regu'       => $reguClean,
                    'status'     => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            echo "Restoring " . count($finalUsers) . " pejabat & danru users...\n";
            User::truncate();
            foreach ($finalUsers as $userData) {
                User::create($userData);
            }
            echo "Users restored successfully!\n";
        }

        Schema::enableForeignKeyConstraints();
    }
}