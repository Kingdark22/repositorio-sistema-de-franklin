<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbHelper
{
    protected static ?string $connectionName = null;

    protected static bool $resolved = false;

    protected static bool $usingIntranet = false;

    protected static ?string $intranetHost = null;

    protected static ?int $intranetPort = null;

    protected const CACHE_TTL = 60;

    protected const CACHE_KEY = 'dbhelper_intranet_available';

    /**
     * Retorna el nombre de la conexión activa. Si la intranet está caída, retorna 'simulacion' como fallback.
     */
    public static function connection()
    {
        if (self::$resolved && self::$connectionName !== null) {
            return self::$connectionName;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached === 'intranet') {
            self::$connectionName = 'intranet';
            self::$usingIntranet = true;
            self::$resolved = true;
            return self::$connectionName;
        }
        if ($cached === 'simulacion') {
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
            self::$resolved = true;
            return self::$connectionName;
        }

        if (self::intranetAlcanzable()) {
            try {
                DB::connection('intranet')->getPdo();
                self::$connectionName = 'intranet';
                self::$usingIntranet = true;
                Cache::put(self::CACHE_KEY, 'intranet', now()->addSeconds(self::CACHE_TTL));
            } catch (\Exception $e) {
                Log::warning('Intranet no disponible (PDO): ' . $e->getMessage());
                self::$connectionName = 'simulacion';
                self::$usingIntranet = false;
                Cache::put(self::CACHE_KEY, 'simulacion', now()->addSeconds(self::CACHE_TTL));
            }
        } else {
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
            Cache::put(self::CACHE_KEY, 'simulacion', now()->addSeconds(self::CACHE_TTL));
        }

        self::$resolved = true;

        return self::$connectionName;
    }

    /**
     * Verifica si el host:puerto de intranet es alcanzable con un socket rápido (300ms).
     */
    protected static function intranetAlcanzable(): bool
    {
        if (self::$intranetHost === null) {
            self::$intranetHost = (string) config('database.connections.intranet.host', '');
            self::$intranetPort = (int) config('database.connections.intranet.port', 5432);
        }

        if (self::$intranetHost === '' || self::$intranetPort <= 0) {
            return false;
        }

        $errno = null;
        $errstr = '';
        $fp = @fsockopen(self::$intranetHost, self::$intranetPort, $errno, $errstr, 0.3);

        if ($fp) {
            fclose($fp);
            return true;
        }

        return false;
    }

    public static function isUsingIntranet(): bool
    {
        self::connection();

        return self::$usingIntranet;
    }

    public static function reset(): void
    {
        self::$connectionName = null;
        self::$resolved = false;
        self::$usingIntranet = false;
        self::$intranetHost = null;
        self::$intranetPort = null;
        Cache::forget(self::CACHE_KEY);
    }
}
