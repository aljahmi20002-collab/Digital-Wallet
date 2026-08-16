<?php

namespace App\Services\Bitnob;

use App\Exceptions\NotifyErrorException;

class BitnobException extends NotifyErrorException
{
    /**
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * @param array<string, mixed> $context
     */
    public static function fromResponse(string $message, array $context = []): self
    {
        $e          = new self($message);
        $e->context = $context;

        return $e;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
