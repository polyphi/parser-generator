<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar;

abstract class GrammarNode
{
    /**
     * Returns the grammar code for this node.
     *
     * @return string
     */
    abstract public function toString(): string;

    /** @see toString() */
    public function __toString(): string
    {
        return $this->toString();
    }
}
