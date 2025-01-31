<?php

namespace l3043y\Common\Data;

use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Throwable;

class ApiResponse extends Data
{
    public function __construct(
        public int             $code,
        public string|Optional $status,
        public string|Optional $message,
        public array|Optional  $debug,
        public array|Optional  $meta,
        public mixed           $data = null,
    )
    {

    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        $paginator = $paginator->withQueryString();
        return self::create([
            'code' => 200,
            'data' => $paginator->items(),
            'meta' => [
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    public static function create(array $payload): self
    {
        $code = $payload['code'] ?? null;
        try {
            $response = new Response($payload['code']);
            return self::from([
                ...$payload,
                'message' => $response->getReasonPhrase()
            ]);
        } catch (\Throwable $e) {
            $response = new Response(400);
        }
        return self::from([
            ...$payload,
            'code' => $response->getStatusCode(),
            'message' => $response->getReasonPhrase(),
            'debug' => [
                'code' => $code,
                ...$payload['debug'] ?? [],
            ]
        ]);
    }

    /**
     * @throws Throwable
     */
    public static function getDebug(mixed $debug): self
    {
        try {
            if ($debug instanceof RequestException) {
                return self::create([
                    'code' => $debug->response->getStatusCode(),
                    'message' => $debug->response->getReasonPhrase(),
                    'debug' => [
                        'type' => RequestException::class,
                        'message' => $debug->getMessage(),
                        'body' => $debug->response->getBody(),
                        'headers' => $debug->response->getHeaders(),
                        'five_traces' => array_slice($debug->getTrace(), 0, 5)
                    ]
                ]);
            } elseif ($debug instanceof ConnectionException) {
                return self::create([
                    'debug' => [
                        'code' => $debug->getCode(),
                        'type' => ConnectionException::class,
                        'message' => $debug->getMessage(),
                        'five_traces' => array_slice($debug->getTrace(), 0, 5)
                    ]
                ]);
            }
            return self::create([
                'debug' => [
                    'raw' => $debug,
                    'type' => gettype($debug),
                    'stringed' => json_encode($debug),
                    'array' => (array)$debug,
                ]
            ]);
        } catch (\Throwable $e) {
            config('common.debug', false) && throw $e;
            return self::create([]);
        }
    }

    public function toResponse($request): JsonResponse
    {
        $response = parent::toResponse($request);
        return response()->json($response->original, $this->code);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if (isset($array['code'])) {
            unset($array['code']);
        }
        if (isset($array['message'])) {
            unset($array['message']);
        }
        $status = isset($this->message) ? "{$this->code} {$this?->message}" : $this->code;
        return [
            'status' => $status,
            ...$array
        ];
    }
}
