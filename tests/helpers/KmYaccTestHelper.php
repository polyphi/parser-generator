<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Helpers;

use RuntimeException;

class KmYaccTestHelper
{
    public static function getTempDir(): string
    {
        $dir = sys_get_temp_dir() . '/polyphi-parser-generator';

        if (!is_dir($dir) && !@mkdir($dir)) {
            throw new RuntimeException('Failed to create temp directory');
        }

        return $dir;
    }

    public static function getTempFile(string $name): string
    {
        $file = static::getTempDir() . DIRECTORY_SEPARATOR . $name;
        if (file_exists($file)) {
            unlink($file);
        }

        return $file;
    }

    /**
     * @param string $contents
     *
     * @return resource
     */
    public static function createStream(string $contents)
    {
        $stream = fopen('php://temp', 'rw');
        if (!$stream) {
            throw new RuntimeException('Failed to create stream');
        }

        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }
}
