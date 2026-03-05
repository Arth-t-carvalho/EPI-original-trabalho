<?php

namespace App\Domain\Model;

class CollectedItem
{
    private ?int $id;
    private string $code;
    private \DateTimeImmutable $timestamp;

    public function __construct(string $code, ?int $id = null, ?\DateTimeImmutable $timestamp = null)
    {
        $this->id = $id;
        $this->code = $code;
        $this->timestamp = $timestamp ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }
}
