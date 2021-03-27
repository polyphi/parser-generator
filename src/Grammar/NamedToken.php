<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar;

class NamedToken extends Token
{
    /** @var string */
    protected $name;

    /**
     * Constructor.
     *
     * @param string $name The name of the token.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Retrieves the name of the token.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritDoc */
    public function toString(): string
    {
        return $this->name;
    }
}
