<?php

declare(strict_types=1);

namespace App\Domain\PublicKey;

use Webauthn\PublicKeyCredentialSource;

class PublicKeyCredentialSourceCollection implements \Iterator
{
    /**
     * @var PublicKeyCredentialSource[]
     */
    private array $credentials;

    private int $position;

    public function __construct(array $credentials)
    {
        $this->credentials = \array_filter($credentials, fn($value) => $value instanceof PublicKeyCredentialSource);
        $this->position = 0;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->credentials[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->credentials[$this->position]);
    }

    /**
     * @return PublicKeyCredentialSource[]
     */
    public function toArray(): array
    {
        return $this->credentials;
    }
}
