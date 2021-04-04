<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar\Nodes;

use Polyphi\Parsers\Utils\InsertArrayCapableTrait;

class Rule extends GrammarNode
{
    use InsertArrayCapableTrait {
        insertArray as protected;
    }

    /** @var Token[] */
    protected $tokens;

    /** @var string */
    protected $code;

    /** @var Token|null */
    protected $prec;

    /**
     * Constructor.
     *
     * @param Token[]    $tokens The list of tokens for the rule.
     * @param string     $code   The code for the rule, without the curly braces.
     * @param Token|null $prec   Optional precedence override. Gives the rule the same precedence level as this token.
     */
    public function __construct(array $tokens = [], string $code = '', ?Token $prec = null)
    {
        $this->tokens = $tokens;
        $this->code = $code;
        $this->prec = $prec;
    }

    /**
     * Retrieves the tokens for the rule.
     *
     * @return Token[] A list of token instances.
     */
    public function getTokens(): array
    {
        return $this->tokens;
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
     * Retrieves the precedence directive.
     *
     * @return Token|null The token that this rule's precedence is equal to, or null.
     */
    public function getPrecToken(): ?Token
    {
        return $this->prec;
    }

    /**
     * Creates a derived copy of the rule with different tokens.
     *
     * @param Token[] $tokens The list of new tokens.
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
     * Creates a derived copy of the rule with added tokens.
     *
     * @param Token[]  $tokens The list of tokens to add.
     * @param int|null $idx    Optional index for where to add the new tokens in the existing list.
     *
     * @return static The created instance.
     */
    public function withAddedTokens(array $tokens, ?int $idx = null): self
    {
        $new = clone $this;
        $new->tokens = $this->insertArray($new->tokens, $tokens, $idx);

        return $new;
    }

    /**
     * Creates a derived copy of the rule with different code.
     *
     * @param string $code The string of new code.
     *
     * @return static The created instance.
     */
    public function withCode(string $code): self
    {
        $new = clone $this;
        $new->code = $code;

        return $new;
    }

    /**
     * Creates a derived copy of the rule with different precedence directive.
     *
     * @param Token|null $token The precedence token, or null for no precedence directive.
     *
     * @return static The created instance.
     */
    public function withPrec(?Token $token): self
    {
        $new = clone $this;
        $new->prec = $token;

        return $new;
    }

    /** @inheritDoc */
    public function toString(): string
    {
        $tokens = array_map(function (Token $token) {
            $tokenStr = trim($token->toString());

            return empty($tokenStr) ? '/* empty */' : $tokenStr;
        }, $this->tokens);

        $tokens = implode(' ', $tokens);
        $action = empty($this->code) ? '' : "{ $this->code }";
        $prec = $this->prec
            ? ' %prec ' . $this->prec->toString()
            : '';

        return trim("{$tokens}$prec $action");
    }
}
