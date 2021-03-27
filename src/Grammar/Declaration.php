<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar;

use Polyphi\Parsers\Utils\InsertArrayCapableTrait;

class Declaration
{
    use InsertArrayCapableTrait {
        insertArray as protected;
    }

    public const TYPE_TOKEN = 'token';
    public const TYPE_LEFT = 'left';
    public const TYPE_RIGHT = 'right';
    public const TYPE_NONASSOC = 'nonassoc';
    public const TYPE_UNION = 'union';
    public const TYPE_TYPE = 'type';

    /**
     * @var string
     * @psalm-var Declaration::TYPE_*
     */
    protected $type;

    /** @var string[] */
    protected $values;

    /**
     * Constructor.
     *
     * @param string                    $type   One of the TYPE_* constants in {@link Declaration}.
     * @param string[]                  $values A list of string values for the declaration's values.
     *
     * @psalm-param Declaration::TYPE_* $type
     */
    public function __construct(string $type, array $values = [])
    {
        $this->type = $type;
        $this->values = $values;
    }

    /**
     * Retrieves the type of the declaration.
     *
     * @return string One of the TYPE_* constants in {@link Declaration}.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Retrieves the values of the declaration.
     *
     * @return string[] A list of strings.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Creates a derived copy of the declaration with a different type.
     *
     * @param string                    $type The new type; one of the TYPE_* constants in {@link Declaration}.
     *
     * @psalm-param Declaration::TYPE_* $type
     *
     * @return static The created instance.
     */
    public function withType(string $type): self
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    /**
     * Creates a derived copy of the declaration with different values.
     *
     * @param string[] $values The new values as a list of strings.
     *
     * @return static The created instance.
     */
    public function withValues(array $values): self
    {
        $new = clone $this;
        $new->values = $values;

        return $new;
    }

    /**
     * Creates a derived copy of the declaration with added values.
     *
     * @param string[] $values The values to add, as a list of strings.
     * @param int|null $idx    Optional index for where to add the new values in the existing list.
     *
     * @return static The created instance.
     */
    public function withAddedValues(array $values, ?int $idx = null): self
    {
        $new = clone $this;
        $new->values = $this->insertArray($new->values, $values, $idx);

        return $new;
    }

    /**
     * Transforms the declaration instance into the equivalent Yacc grammar syntax.
     *
     * @return string A string containing grammar code for the declaration.
     */
    public function toString(): string
    {
        $valueStr = implode(' ', $this->values);

        return trim("%{$this->type} {$valueStr}");
    }

    /**
     * Casts the declaration to a string.
     *
     * @see Declaration::toString()
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
