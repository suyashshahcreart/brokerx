<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RewriteImagePaths
{
    /**
     * Ensure any leading "/images/" references inside HTML are rewritten
     * to use the application's asset base so subfolder deployments work
     * without changing every Blade file.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Only process typical HTML responses
        $contentType = $response->headers->get('Content-Type');
        if (!$contentType || stripos($contentType, 'text/html') === false) {
            return $response;
        }

        $html = $response->getContent();
        if (!is_string($html) || $html === '') {
            return $response;
        }

        $assetPrefix = rtrim(asset('images'), '/') . '/';

        $search = [
            'src="/images/',
            "src='/images/",
            'href="/images/',
            "href='/images/",
            'url("/images/',
            "url('/images/",
            'url(/images/',
            'content: url(/images/',
        ];

        $replace = [
            'src="' . $assetPrefix,
            "src='" . $assetPrefix,
            'href="' . $assetPrefix,
            "href='" . $assetPrefix,
            'url("' . $assetPrefix,
            "url('" . $assetPrefix,
            'url(' . $assetPrefix,
            'content: url(' . $assetPrefix,
        ];

        $updated = str_replace($search, $replace, $html);
        if ($updated !== $html) {
            $response->setContent($updated);
        }

        return $response;
    }
}


