<?php

declare(strict_types=1);

namespace OpenAI\Responses;

class ChatChoice
{
    public function __construct(
        public readonly int $index,
        public readonly array $message,
        public readonly ?string $finishReason,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            index:        $data['index'] ?? 0,
            message:      $data['message'] ?? [],
            finishReason: $data['finish_reason'] ?? null,
            raw:          $data
        );
    }
}
