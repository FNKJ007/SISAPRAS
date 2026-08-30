<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized cache management service for API (Aplikasi Pemeliharaan & Inspeksi Sarana).
 * 
 * Provides tagged cache keys and bulk invalidation when data changes.
 * Uses file cache driver (no Redis needed) — safe for production.
 */
class CacheService
{
    /**
     * Cache TTL in seconds for different data types.
     */
    const TTL_STATS    = 300;  // 5 minutes — KPI/statistics
    const TTL_LIST     = 300;  // 5 minutes — dropdown lists (pos, pegawai)
    const TTL_DASHBOARD = 120; // 2 minutes — dashboard (more fresh)

    /**
     * Cache key prefixes grouped by data domain.
     */
    const DOMAIN_KEYS = [
        'unit'       => ['unit_kpi', 'unit_list', 'unit_types', 'admin_dashboard_stats_', 'admin_dashboard_absen_unit', 'pos_kpi', 'pos_unit_dist'],
        'pos'        => ['pos_kpi', 'pos_list', 'active_pos_objects', 'pos_unit_dist', 'admin_dashboard_stats_'],
        'user'       => ['user_kpi', 'user_list', 'pegawai_list', 'danru_list', 'officials_pegawai', 'admin_dashboard_stats_', 'pengaturan_meta'],
        'peralatan'  => ['peralatan_kpi', 'peralatan_list', 'admin_dashboard_stats_'],
        'regu'       => ['regu_list', 'regu_kpi', 'officials_regu', 'danru_list'],
        'bidang'     => ['bidang_list', 'bidang_kpi', 'pengaturan_meta'],
        'pengajuan'  => ['pengajuan_kpi', 'admin_dashboard_stats_'],
        'cek_unit'   => ['cek_unit_kpi', 'admin_dashboard_stats_', 'admin_dashboard_absen_unit'],
        'cek_alat'   => ['cek_alat_kpi', 'admin_dashboard_stats_'],
        'invoice'    => ['invoice_kpi', 'admin_dashboard_stats_'],
    ];

    /**
     * Invalidate all cache keys related to a data domain.
     * Call this after store/update/destroy operations.
     *
     * @param string|array $domains  e.g. 'unit', ['unit', 'pos'], etc.
     */
    public static function invalidate(string|array $domains): void
    {
        $domains = (array) $domains;
        $flushedPrefixes = [];

        foreach ($domains as $domain) {
            $prefixes = self::DOMAIN_KEYS[$domain] ?? [];
            foreach ($prefixes as $prefix) {
                if (in_array($prefix, $flushedPrefixes)) {
                    continue;
                }
                $flushedPrefixes[] = $prefix;

                // For year-based keys like 'admin_dashboard_stats_', flush all years
                if (str_ends_with($prefix, '_')) {
                    for ($y = 2024; $y <= (int) date('Y') + 1; $y++) {
                        Cache::forget($prefix . $y);
                    }
                } else {
                    Cache::forget($prefix);
                }
            }
        }
    }

    /**
     * Remember a value in cache with the standard TTL for stats.
     */
    public static function rememberStats(string $key, \Closure $callback, ?int $ttl = null)
    {
        try {
            $cached = Cache::get($key);
            if ($cached !== null && !($cached instanceof \__PHP_Incomplete_Class)) {
                return $cached;
            }
        } catch (\Throwable $e) {
            Cache::forget($key);
        }

        $fresh = $callback();
        try {
            Cache::put($key, $fresh, $ttl ?? self::TTL_STATS);
        } catch (\Throwable $e) {
            // Ignore caching errors
        }
        return $fresh;
    }

    /**
     * Remember a list in cache safely converting Eloquent Collections to pure arrays
     * to prevent __PHP_Incomplete_Class errors across PHP processes and CLI workers.
     */
    public static function rememberList(string $key, \Closure $callback, ?int $ttl = null)
    {
        try {
            $cached = Cache::get($key);
            if ($cached !== null) {
                if (is_array($cached)) {
                    return collect($cached)->map(function ($item) {
                        return is_array($item) ? (object) $item : $item;
                    });
                }
                if ($cached instanceof \Illuminate\Support\Collection) {
                    return $cached;
                }
                Cache::forget($key);
            }
        } catch (\Throwable $e) {
            Cache::forget($key);
        }

        $fresh = $callback();

        // Convert Collection of Models/objects to pure arrays for safe file-cache storage
        if ($fresh instanceof \Illuminate\Support\Collection) {
            $arrayData = $fresh->map(function ($item) {
                if ($item instanceof \Illuminate\Database\Eloquent\Model) {
                    return $item->getAttributes();
                }
                if (is_object($item)) {
                    return (array) $item;
                }
                return $item;
            })->values()->toArray();

            try {
                Cache::put($key, $arrayData, $ttl ?? self::TTL_LIST);
            } catch (\Throwable $e) {
                // Ignore caching errors
            }

            return collect($arrayData)->map(function ($item) {
                return is_array($item) ? (object) $item : $item;
            });
        }

        if (is_array($fresh)) {
            try {
                Cache::put($key, $fresh, $ttl ?? self::TTL_LIST);
            } catch (\Throwable $e) {
                // Ignore caching errors
            }

            return collect($fresh)->map(function ($item) {
                return is_array($item) ? (object) $item : $item;
            });
        }

        return $fresh;
    }
}
