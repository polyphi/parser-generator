<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar\Nodes;

use Polyphi\Parsers\Utils\InsertArrayCapableTrait;

class Declaration extends GrammarNode
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

    /** @var string */
    protected $type;

    /** @var Token[] */
    protected $tokens;

    /**
     * Constructor.
     *
     * @param string  $type   One of the TYPE_* constants in {@link Declaration}.
     * @param Token[] $tokens A list of tokens.
     */
    public function __construct(string $type, array $tokens = [])
    {
        $this->type = $type;
        $this->tokens = $tokens;
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
     * Retrieves the tokens of the declaration.
     *
     * @return Token[] A list of strings.
     */
    public function getTokens(): array
    {
        return $this->tokens;
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
     * Creates a derived copy of the declaration with different tokens.
     *
     * @param Token[] $tokens The new tokens.
     *
     * @return static The created instance.
     */
    public function withTokens(array $tokens): self
    {
        $new = clone $this;
        $new->tokens = $tokens;

        return $new;
    }

    /**
     * Creates a derived copy of the declaration with added tokens.
     *
     * @param Token[]  $values The list of tokens to add.
     * @param int|null $idx    Optional index for where to add the new values in the existing list.
     *
     * @return static The created instance.
     */
    public function withAddedTokens(array $values, ?int $idx = null): self
    {
        $new = clone $this;
        $new->tokens = $this->insertArray($new->tokens, $values, $idx);

        return $new;
    }

    /** @inheritDoc */
    public function toString(): string
    {
        $tokens = array_map(function (Token $token): string {
            return $token->toString();
        }, $this->tokens);

        $tokens = implode(' ', $tokens);

        return trim("%{$this->type} {$tokens}");
    }
}
