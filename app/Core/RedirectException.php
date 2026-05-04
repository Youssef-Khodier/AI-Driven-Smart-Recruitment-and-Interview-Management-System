<?php

namespace App\Core;

final class RedirectException extends \RuntimeException
{
    public function __construct(
        private readonly string $location,
        private readonly int $status = 302,
    ) {
        parent::__construct('Redirecting');
    }

    public function location(): string
    {
        return $this->location;
    }

    public function status(): int
    {
        return $this->status;
    }
}
