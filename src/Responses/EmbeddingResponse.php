<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Responses;

class EmbeddingResponse
{
    /** @param EmbeddingData[] $data */
    private function __construct(
        public readonly string $model,
        public readonly array $data,
        public readonly UsageResponse $usage,
        public readonly array $raw
    ) {}

    public static function fromArray(array $raw): self
    {
        return new self(
            model: $raw['model'] ?? '',
            data:  array_map(
                fn (array $item) => EmbeddingData::fromArray($item),
                $raw['data'] ?? []
            ),
            usage: UsageResponse::fromArray($raw['usage'] ?? []),
            raw:   $raw
        );
    }

    /** Return the embedding vector for the first item. */
    public function embedding(): array
    {
        return $this->data[0]?->embedding ?? [];
    }
}
