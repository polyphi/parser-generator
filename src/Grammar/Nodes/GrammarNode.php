<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar\Nodes;

abstract class GrammarNode
{
    /**
     * Generates the YACC grammar code snippet for this node.
     *
     * @return string The generated Yacc code snippet.
     */
    abstract public function toString(): string;

    /** @see toString() */
    public function __toString(): string
    {
        return $this->toString();
    }
}
