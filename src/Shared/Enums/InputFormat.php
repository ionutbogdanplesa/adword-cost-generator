<?php
namespace AdWords\Shared\Enums;

use InvalidArgumentException;

enum InputFormat: string
{
    case CSV = 'csv';
    case JSON = 'json';

    public static function fromFilename(string $filename): self
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return match($extension) {
            'csv' => self::CSV,
            'json' => self::JSON,
            default => throw new InvalidArgumentException('Unsupported file format. Supported formats: csv, json')
        };
    }
}
