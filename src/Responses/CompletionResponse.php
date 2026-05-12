<?php

declare(strict_types=1);

namespace OpenAI\Responses;

class CompletionResponse
{
    /** @param CompletionChoice[] $choices */
    private function __construct(
        public readonly string $id,
        public readonly string $model,
        public readonly int $created,
        public readonly array $choices,
        public readonly UsageResponse $usage,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:      $data['id'] ?? '',
            model:   $data['model'] ?? '',
            created: $data['created'] ?? 0,
            choices: array_map(
                fn (array $c) => CompletionChoice::fromArray($c),
                $data['choices'] ?? []
            ),
            usage:   UsageResponse::fromArray($data['usage'] ?? []),
            raw:     $data
        );
    }

    public function text(): string
    {
        return $this->choices[0]?->text ?? '';
    }
}
