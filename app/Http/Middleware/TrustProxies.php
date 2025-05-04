<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Fideloper\Proxy\TrustProxies as Middleware;

/**
 * @class TrustProxies
 * @brief Gestiona los middleware para los proxies de confianza
 *
 * Gestiona los middleware para los proxies de confianza
 */
class TrustProxies extends Middleware
{
    /**
     * Los proxies de confianza para esta aplicación.
     *
     * @var array|string $proxies
     */
    protected $proxies;

    /**
     * Los encabezados que deben usarse para detectar proxies.
     *
     * @var int $headers
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
