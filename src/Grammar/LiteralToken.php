<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar;

class LiteralToken extends Token
{
    /** @var string */
    protected $value;

    /**
     * Constructor.
     *
     * @param string $value The string value of the literal, without quotes.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Retrieves the string value of the literal, without quotes.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /** @inheritDoc */
    public function toString(): string
    {
        return "'{$this->value}'";
    }
}
