<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

abstract class AbstractJsonRepository
{
    private string $filename;

    protected function loadData(): array
    {
        $filename = $this->getFileName();
        if (!\file_exists($filename)) {
            return [];
        }
        $data = \file_get_contents($filename);
        if (empty($data)) {
            return [];
        }

        $data = \json_decode($data, true);
        if ((empty($data)) || (!\is_array($data))) {
            return [];
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->jsonUnserialize($value);
        }

        return $data;
    }

    protected function jsonUnserialize(mixed $value): mixed
    {
        return $value;
    }

    protected function writeData(array $data): void
    {
        $result = \file_put_contents(
            $this->getFileName(),
            \json_encode($data, \JSON_PRETTY_PRINT),
            \LOCK_EX
        );
        if (!$result) {
            throw new \RuntimeException('Houve um erro ao salvar os dados');
        }
    }

    private function getFileName(): string
    {
        if (!isset($this->filename)) {
            $class = \get_called_class();
            $class = \substr($class, \strrpos($class, "\\") + 1);
            $this->filename = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . $class . '.json';
        }
        return $this->filename;
    }
}
