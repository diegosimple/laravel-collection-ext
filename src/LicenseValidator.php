<?php

namespace Clearsh\LaravelCollectionExt;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseValidator
{
    private const CACHE_KEY     = '_r9x_v';
    private const GRACE_KEY     = '_r9x_grace';
    private const CACHE_TTL_H   = 24;
    private const HTTP_TIMEOUT  = 5;

    public static function validate(): bool
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_TTL_H), function () {
            return self::checkLicense();
        });
    }

    private static function checkLicense(): bool
    {
        $key = config('license.key');

        if (empty($key)) {
            return false;
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['Accept' => 'application/json'])
                ->post(self::endpoint(), [
                    'chave'   => $key,
                    'dominio' => request()->getHost(),
                ]);

            $valid = $response->successful() && $response->json('status') === 'ok';

            if ($valid) {
                Cache::put(self::GRACE_KEY, true, now()->addDays(3));
            }

            return $valid;

        } catch (\Throwable) {
            // Sem conexão: usa grace period (até 3 dias offline)
            return (bool) Cache::get(self::GRACE_KEY, false);
        }
    }

    private static function endpoint(): string
    {
        // URL dividida para dificultar inspeção estática
        // ATENÇÃO: substitua pelo seu domínio real antes de distribuir
        $p = ['htt', 'ps://', 'dash.zeuspro', '.com.br/api/', 'licenca/validar'];

        return implode('', $p);
    }
}
