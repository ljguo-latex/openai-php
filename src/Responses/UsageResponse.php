<?php

declare(strict_types=1);

namespace OpenAI\Responses;

class UsageResponse
{
    public function __construct(
        public readonly int $promptTokens,
        public readonly int $completionTokens,
        public readonly int $totalTokens,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            promptTokens:     $data['prompt_tokens'] ?? 0,
            completionTokens: $data['completion_tokens'] ?? 0,
            totalTokens:      $data['total_tokens'] ?? 0,
            raw:              $data
        );
    }
}
