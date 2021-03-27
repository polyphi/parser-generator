<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Utils;

trait InsertArrayCapableTrait
{
    /**
     * Inserts an array into another, with support for inserting into a specific index.
     *
     * @psalm-template T
     *
     * @param T[]      $target The target array.
     * @param T[]      $add    The array to insert or append.
     * @param int|null $idx    The index for where to insert the second array into the first, or null to append to the
     *                         end of the target array.
     *
     * @return T[] The resulting array.
     */
    public function insertArray(array $target, array $add, ?int $idx = null): array
    {
        if ($idx === null) {
            $target = array_merge($target, $add);
        } else {
            array_splice($target, $idx, 0, $add);
        }

        return $target;
    }
}
