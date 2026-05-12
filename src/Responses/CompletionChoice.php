<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Responses;

class CompletionChoice
{
    public function __construct(
        public readonly int $index,
        public readonly string $text,
        public readonly ?string $finishReason,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            index:        $data['index'] ?? 0,
            text:         $data['text'] ?? '',
            finishReason: $data['finish_reason'] ?? null,
            raw:          $data
        );
    }
}
