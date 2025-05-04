<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/**
 * @class SecurityHeaders
 * @brief Configuración de cabeceras de seguridad
 *
 * Configuración de cabeceras de seguridad
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SecurityHeaders
{
    /**
     * Headers de cabeceras no deseadas
     *
     * @var array $unwantedHeaders
     */
    private $unwantedHeaders = ['X-Powered-By', 'server', 'Server'];

    /**
     * Gestiona peticiones entrantes
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $nonce = $request->session()->has('nonce')
            ? $request->session()->get('nonce')
            : Str::random(12);

        if (! $request->session()->has('nonce')) {
            $request->session()->put('nonce', $nonce);
        }

        if (app()->environment('production')) {
            $response->headers->set(
                'Referrer-Policy',
                env('REFERRER_POLICY', 'no-referrer-when-downgrade')
            );
            $response->headers->set(
                'X-XSS-Protection',
                env('X_XSS_PROTECTION', '1; mode=block')
            );
            $response->headers->set(
                'Content-Security-Policy',
                env('CONTENT_SECURITY_POLICY', "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' *.cloudflare.com fonts.googleapis.com 'unsafe-inline'; img-src 'self' * data:; font-src 'self' fonts.gstatic.com data: ; connect-src 'self'; media-src 'self'; frame-src 'self'; object-src 'none'; base-uri 'self';")
            );
            $response->headers->set(
                'Access-Control-Allow-Origin',
                env('APP_URL')
            );
            $response->headers->set(
                'Access-Control-Allow-Methods',
                env('ACCESS_CONTROL_ALLOW_METHODS', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            );
            $response->headers->set(
                'Access-Control-Allow-Headers',
                env('ACCESS_CONTROL_ALLOW_HEADERS', 'Origin, Content-Type, Accept, Authorization, X-Requested-With,X-CSRF-Token')
            );
            $response->headers->set(
                'X-Frame-Options',
                env('X_FRAME_OPTIONS', 'deny')
            );
            $response->headers->set(
                'X-Content-Type-Options',
                env('X_CONTENT_TYPE_OPTIONS', 'nosniff')
            );
            $response->headers->set(
                'X-Permitted-Cross-Domain-Policies',
                env('X_PERMITTED_CROSS_DOMAIN_POLICIES', 'none')
            );
            $response->headers->set(
                'Strict-Transport-Security',
                env('STRICT_TRANSPORT_SECURITY', 'max-age=31536000; includeSubDomains; preload')
            );

            $this->removeUnwantedHeaders($this->unwantedHeaders);
        }

        return $response;
    }

    /**
     * Remueve las cabeceras no deseadas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param $headers
     *
     * @return void
     */
    private function removeUnwantedHeaders($headers): void
    {
        foreach ($headers as $header) {
            header_remove($header);
        }
    }
}
