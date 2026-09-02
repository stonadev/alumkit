<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the compiled package assets straight from vendor.
 *
 * A controller (not a closure) keeps `route:cache` free of serialized
 * closures and absolute paths, so `composer update stonadev/alumkit`
 * alone ships new CSS/JS. `Cache-Control: no-cache` forces revalidation
 * every request — the file is served from vendor and updates ship
 * rapidly, so a long-lived public cache would serve stale styles.
 */
class AssetController extends Controller
{
    /**
     * Whitelisted asset names mapped to their content types.
     *
     * @var array<string, string>
     */
    private const array TYPES = [
        'alumkit.css' => 'text/css',
        'alumkit-editor.css' => 'text/css',
        'alumkit-editor.js' => 'application/javascript',
        'alumkit-sortable.esm.js' => 'application/javascript',
    ];

    /**
     * Serve a compiled package asset.
     */
    public function __invoke(Request $request, string $file): BinaryFileResponse
    {
        $type = self::TYPES[$file] ?? null;

        $path = __DIR__.'/../../../public/'.$file;

        if ($type === null || ! is_file($path)) {
            abort(404);
        }

        // `no-cache, public`: browsers and shared caches must revalidate
        // before reuse — the file is served from vendor and updates ship
        // rapidly, so a long-lived public cache would serve stale styles.
        $response = response()->file($path, [
            'Content-Type' => $type,
            'Cache-Control' => 'no-cache',
        ]);

        // This bare route skips the `web` group, so handle conditional
        // requests here instead of via CheckResponseForModifications.
        $response->isNotModified($request);

        return $response;
    }
}
