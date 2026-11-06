<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * Middleware TrustHosts
 *
 * Mengonfigurasi pola domain host yang dipercaya untuk mencegah eksploitasi manipulasi *HTTP Host Header*.
 */
class TrustHosts extends Middleware
{
    /**
     * Menghasilkan daftar pola nama host yang dipercaya oleh aplikasi SINFAS.
     *
     * @return array<int, string|null> Pola subdomain dari URL aplikasi utama.
     */
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
