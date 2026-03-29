<?php

namespace CyberShield\Security\Signatures;

class Signature
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $severity,
        public readonly float $impactScore,
        public readonly array $patterns,
        public readonly array $tags = [],
        public readonly array $metadata = []
    ) {
    }

    /**
     * Create a Signature instance from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? uniqid('sig_'),
            name: $data['name'],
            description: $data['description'] ?? '',
            severity: $data['severity'] ?? 'medium',
            impactScore: (float) ($data['impact_score'] ?? 5.0),
            patterns: $data['patterns'] ?? [['regex' => $data['regex'] ?? '']],
            tags: $data['tags'] ?? [],
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Get the regex patterns.
     */
    public function getRegexPatterns(): array
    {
        return array_column($this->patterns, 'regex');
    }
}
