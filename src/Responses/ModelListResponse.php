<?php

declare(strict_types=1);

namespace OpenAI\Responses;

class ModelListResponse
{
    /** @param ModelResponse[] $data */
    public function __construct(
        public readonly array $data,
        public readonly array $raw
    ) {}

    public static function fromArray(array $raw): self
    {
        return new self(
            data: array_map(
                fn (array $item) => ModelResponse::fromArray($item),
                $raw['data'] ?? []
            ),
            raw: $raw
        );
    }

    /** @return string[] */
    public function ids(): array
    {
        return array_map(fn (ModelResponse $m) => $m->id, $this->data);
    }
}
