<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Responses;

class EmbeddingData
{
    public function __construct(
        public readonly int $index,
        public readonly array $embedding,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            index:     $data['index'] ?? 0,
            embedding: $data['embedding'] ?? [],
            raw:       $data
        );
    }
}
