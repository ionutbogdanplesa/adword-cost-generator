<?php

namespace AdWords\Shared\Outputs;

use AdWords\Shared\Interfaces\ReportOutputInterface;
use Exception;
use RuntimeException;

class FileOutput implements ReportOutputInterface
{
    private string $outputPath;

    public function __construct(string $outputPath)
    {
        $this->validateAndSetPath($outputPath);
    }

    public function output(string $formattedReport): void
    {
        try {
            file_put_contents($this->outputPath, $formattedReport);
        } catch (Exception $e) {
            throw new RuntimeException(
                "Error writing report to file: {$this->outputPath}. " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function validateAndSetPath(string $path): void
    {
        $absolutePath = realpath(dirname($path)) . DIRECTORY_SEPARATOR . basename($path);

        $directory = dirname($absolutePath);
        if (!is_dir($directory)) {
            throw new RuntimeException("Directory does not exist: {$directory}");
        }

        if (!is_writable($directory)) {
            throw new RuntimeException("Directory is not writable: {$directory}");
        }

        if (file_exists($absolutePath) && !is_writable($absolutePath)) {
            throw new RuntimeException("File exists but is not writable: {$absolutePath}");
        }

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        if ($extension !== 'csv') {
            throw new RuntimeException("Invalid file extension: {$extension}. Allowed: csv");
        }

        $this->outputPath = $absolutePath;
    }
}
