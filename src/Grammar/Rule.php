<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar;

use Polyphi\Parsers\Utils\InsertArrayCapableTrait;

class Rule
{
    use InsertArrayCapableTrait {
        insertArray as protected;
    }

    /** @var string[] */
    protected $symbols;

    protected $code;

    /**
     * Constructor.
     *
     * @param string[] $symbols The symbols for the rule as a list of strings.
     * @param string   $code    The code for the rule, without the curly braces.
     */
    public function __construct(array $symbols, string $code)
    {
        $this->symbols = $symbols;
        $this->code = $code;
    }

    /**
     * Retrieves the symbols for the rule.
     *
     * @return string[] A list of strings.
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    /**
     * Retrieves the code for the rule.
     *
     * @return string A string containing the code, without the curly braces.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Creates a derived copy of the rule with different symbols.
     *
     * @param string[] $symbols A list of symbol names as strings.
     *
     * @return static The created instance.
     */
    public function withSymbols(array $symbols): self
    {
        $new = clone $this;
        $new->symbols = $symbols;

        return $new;
    }

    /**
     * Creates a derived copy of the rule with added symbols.
     *
     * @param string[] $symbols The list of symbol names as strings.
     * @param int|null $idx     Optional index for where to add the new symbols in the existing list.
     *
     * @return static The created instance.
     */
    public function withAddedSymbols(array $symbols, ?int $idx = null): self
    {
        $new = clone $this;
        $new->symbols = $this->insertArray($new->symbols, $symbols, $idx);

        return $new;
    }

    /**
     * Creates a derived copy of the rule with different code.
     *
     * @param string $action The string of new code.
     *
     * @return static The created instance.
     */
    public function withAction(string $action): self
    {
        $new = clone $this;
        $new->code = $action;

        return $new;
    }

    /**
     * Transforms the rule instance into the equivalent Yacc grammar syntax.
     *
     * @return string A string containing grammar code for the rule.
     */
    public function toString(): string
    {
        $symbolList = array_map(function ($match) {
            return $match === ''
                ? '/* empty */'
                : $match;
        }, $this->symbols);

        $symbolListStr = implode(' ', $symbolList);
        $action = empty($this->code)
            ? ''
            : "{ $this->code }";

        return trim("{$symbolListStr} $action");
    }

    /**
     * Casts the rule to a string.
     *
     * @see Rule::toString()
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
