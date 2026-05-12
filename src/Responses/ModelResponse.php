<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Responses;

class ModelResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $created,
        public readonly string $ownedBy,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:      $data['id'] ?? '',
            object:  $data['object'] ?? '',
            created: $data['created'] ?? 0,
            ownedBy: $data['owned_by'] ?? '',
            raw:     $data
        );
    }
}
