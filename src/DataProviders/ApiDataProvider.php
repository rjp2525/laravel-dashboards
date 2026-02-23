<?php

namespace Reno\Dashboard\DataProviders;

use Closure;
use Illuminate\Support\Facades\Http;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Support\WidgetContext;

class ApiDataProvider implements DataProvider
{
    protected string $method = 'GET';

    /** @var array<string, string> */
    protected array $headers = [];

    /** @var array<string, mixed> */
    protected array $query = [];

    protected ?Closure $responseTransformer = null;

    protected int $timeout = 10;

    public function __construct(
        protected string $url,
    ) {}

    public static function from(string $url): self
    {
        return new self($url);
    }

    public function method(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /** @param array<string, string> $headers */
    public function headers(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /** @param array<string, mixed> $query */
    public function query(array $query): self
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function transform(Closure $callback): self
    {
        $this->responseTransformer = $callback;

        return $this;
    }

    public function fetch(WidgetContext $context): mixed
    {
        $request = Http::timeout($this->timeout)
            ->withHeaders($this->headers);

        $response = match ($this->method) {
            'POST' => $request->post($this->url, $this->query),
            default => $request->get($this->url, $this->query),
        };

        $data = $response->json();

        if ($this->responseTransformer instanceof Closure) {
            return ($this->responseTransformer)($data, $context);
        }

        return $data;
    }
}
