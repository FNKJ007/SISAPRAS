<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized cache management service for SISAPRAS.
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
        'user'       => ['user_kpi', 'user_list', 'pegawai_list'],
        'peralatan'  => ['peralatan_kpi', 'peralatan_list', 'admin_dashboard_stats_'],
        'regu'       => ['regu_list'],
        'bidang'     => ['bidang_list', 'bidang_kpi'],
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
    public static function rememberStats(string $key, \Closure $callback, int $ttl = null)
    {
        return Cache::remember($key, $ttl ?? self::TTL_STATS, $callback);
    }

    /**
     * Remember a value in cache with the standard TTL for lists.
     */
    public static function rememberList(string $key, \Closure $callback, int $ttl = null)
    {
        return Cache::remember($key, $ttl ?? self::TTL_LIST, $callback);
    }
}
