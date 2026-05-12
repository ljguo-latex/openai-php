<?php

declare(strict_types=1);

namespace Ljguo\OpenAI\Responses;

class ChatResponse
{
    /** @param ChatChoice[] $choices */
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
                fn (array $choice) => ChatChoice::fromArray($choice),
                $data['choices'] ?? []
            ),
            usage:   UsageResponse::fromArray($data['usage'] ?? []),
            raw:     $data
        );
    }

    /** Return the text content of the first choice. */
    public function content(): string
    {
        return $this->choices[0]?->message['content'] ?? '';
    }

    /** Return the finish reason of the first choice. */
    public function finishReason(): ?string
    {
        return $this->choices[0]?->finishReason;
    }
}
