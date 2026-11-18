<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Makes form submissions idempotent: the same submission is only processed once.
 *
 * resources/js/idempotency.js gives every form a one-time key. When the same
 * form is sent again with the same data and key (a double click, a retry on a
 * slow connection, a refresh that resends the form), the request waits for the
 * first one and then gets the same response instead of running again.
 *
 * It runs before the session starts, so the duplicate request also sees the
 * session as the first request left it (for example an emptied POS cart).
 * Requests without a key, such as Livewire updates, are passed straight through.
 */
class PreventDuplicateSubmissions
{
    public const FIELD = '_idempotency_key';

    public const HEADER = 'Idempotency-Key';

    /** How long a finished submission is remembered. */
    private const REMEMBER_SECONDS = 600;

    /** How long a duplicate waits for the first request to finish. */
    private const WAIT_SECONDS = 20;

    /** Responses larger than this are not stored for replay. */
    private const MAX_STORED_BYTES = 1_000_000;

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $key = $request->header(self::HEADER) ?? $request->input(self::FIELD);
        // Controllers never see the key.
        $request->request->remove(self::FIELD);
        $request->query->remove(self::FIELD);

        if (! is_string($key) || ! preg_match('/^[A-Za-z0-9_-]{16,100}$/', $key)) {
            return $next($request);
        }

        $cacheKey = 'idempotency:'.hash('sha256', implode('|', [$key, $request->ip(), $this->fingerprint($request)]));
        $lock = Cache::lock($cacheKey.':lock', self::WAIT_SECONDS * 3);

        try {
            $lock->block(self::WAIT_SECONDS);
        } catch (LockTimeoutException) {
            abort(409, 'This was already submitted and is still being processed. Please wait a moment and refresh the page.');
        }

        try {
            if ($stored = Cache::get($cacheKey)) {
                return $this->replay($stored);
            }

            $response = $next($request);

            if ($stored = $this->storable($response)) {
                Cache::put($cacheKey, $stored, self::REMEMBER_SECONDS);
            }

            return $response;
        } finally {
            $lock->release();
        }
    }

    /**
     * The same key with different data (e.g. another barcode typed into the same form) is a new submission.
     */
    private function fingerprint(Request $request): string
    {
        $files = Arr::map(Arr::dot($request->allFiles()), fn ($file) => $file instanceof UploadedFile
            ? [$file->getClientOriginalName(), $file->getSize()]
            : null);

        return hash('sha256', json_encode([
            $request->method(),
            $request->path(),
            Arr::except($request->input(), ['_token']),
            $files,
        ]));
    }

    /**
     * Only finished, successful work is replayed; errors can simply be retried.
     */
    private function storable(Response $response): ?array
    {
        if ($response->getStatusCode() >= 400) {
            return null;
        }

        if ($response instanceof RedirectResponse) {
            return ['redirect' => $response->getTargetUrl(), 'status' => $response->getStatusCode()];
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return null;
        }

        $content = $response->getContent();

        if (! is_string($content) || strlen($content) > self::MAX_STORED_BYTES) {
            return null;
        }

        return [
            'content' => $content,
            'status' => $response->getStatusCode(),
            'type' => $response->headers->get('Content-Type'),
        ];
    }

    private function replay(array $stored): Response
    {
        $response = isset($stored['redirect'])
            ? new RedirectResponse($stored['redirect'], $stored['status'])
            : new Response($stored['content'], $stored['status'], array_filter(['Content-Type' => $stored['type']]));

        $response->headers->set('Idempotent-Replayed', 'true');

        return $response;
    }
}
