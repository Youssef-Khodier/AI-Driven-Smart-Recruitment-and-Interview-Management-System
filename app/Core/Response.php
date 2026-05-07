<?php

namespace App\Core;

final class Response
{
    public function __construct(
        private readonly string $content = '',
        private readonly int $status = 200,
        private readonly array $headers = [],
    ) {
    }

    public static function view(string $view, array $data = [], int $status = 200): self
    {
        return new self(View::render($view, $data), $status);
    }

    public static function redirect(string $location): self
    {
        $base = Request::basePath();

        if ($base !== '' && str_starts_with($location, '/') && ! str_starts_with($location, $base . '/')) {
            $location = $base . $location;
        }

        return new self('', 302, ['Location' => $location]);
    }

    public function with(string $key, mixed $value): self
    {
        Session::flash($key, $value);
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
