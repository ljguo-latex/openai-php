<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Exceptions;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message = '',
        private readonly int $statusCode = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isRateLimitError(): bool
    {
        return $this->statusCode === 429;
    }

    public function isAuthenticationError(): bool
    {
        return $this->statusCode === 401;
    }

    public function isNotFoundError(): bool
    {
        return $this->statusCode === 404;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }
}
