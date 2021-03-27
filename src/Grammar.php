<?php

declare(strict_types=1);

namespace Polyphi\Parsers;

use Polyphi\Parsers\Grammar\Nodes\Declaration;
use Polyphi\Parsers\Grammar\Nodes\Rule;
use Polyphi\Parsers\Utils\InsertArrayCapableTrait;

class Grammar
{
    use InsertArrayCapableTrait {
        insertArray as protected;
    }

    /** @var Declaration[] */
    protected $decls;

    /** @var array<string, Rule[]> */
    protected $rules;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Declaration[]         $decls A list of declaration instances.
     * @param array<string, Rule[]> $rules A mapping of non-terminal names to arrays of {@link Rule} instances.
     */
    public function __construct(array $decls = [], array $rules = [])
    {
        $this->decls = $decls;
        $this->rules = $rules;
    }

    /**
     * Retrieves all the declarations in the grammar.
     *
     * @return Declaration[] A list of declaration instances.
     */
    public function getDeclarations(): array
    {
        return $this->decls;
    }

    /**
     * Retrieves all the rules in the grammar.
     *
     * @return array<string, Rule[]> A mapping of non-terminal string names to arrays of {@link Rule} instances.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Retrieves the rules for a specific non-terminal, by name.
     *
     * @param string $nonTerminal The name of the non-terminal whose rules should be returned.
     *
     * @return Rule[] The rules associated with the non-terminal with the given $name. An empty array will be returned
     *                if the grammar does not contain a non-terminal with the given $name.
     */
    public function getRulesFor(string $nonTerminal): array
    {
        return $this->rules[$nonTerminal] ?? [];
    }

    /**
     * Creates a derived copy of the grammar with different declarations.
     *
     * @param Declaration[] $decls The new declarations.
     *
     * @return static The created instance.
     */
    public function withDeclarations(array $decls): self
    {
        $new = clone $this;
        $new->decls = $decls;

        return $new;
    }

    /**
     * Creates a derived copy of the grammar with added declarations.
     *
     * @param Declaration[] $decls The additional declarations.
     * @param int|null      $idx   Optional index for where to add the new rules in the existing list.
     *
     * @return static The created instance.
     */
    public function withAddedDeclarations(array $decls, ?int $idx = null): self
    {
        $new = clone $this;
        $new->decls = $this->insertArray($new->decls, $decls, $idx);

        return $new;
    }

    /**
     * Creates a derived copy of the grammar with different rules.
     *
     * @param array<string, Rule[]> $rules The new rules.
     *
     * @return static The created instance.
     */
    public function withRules(array $rules): self
    {
        $new = clone $this;
        $new->rules = $rules;

        return $new;
    }

    /**
     * Creates a derived copy of the grammar with added declarations.
     *
     * @param string   $nonTerminal The name of the non-terminal to which the $rules will be added.
     * @param Rule[]   $rules       The rules to add to the non-terminal.
     * @param int|null $idx         Optional index for where to add the new rules in the existing list.
     *
     * @return static The created instance.
     */
    public function withAddedRules(string $nonTerminal, array $rules, ?int $idx = null): self
    {
        $new = clone $this;
        $new->rules[$nonTerminal] = $this->insertArray($new->rules[$nonTerminal] ?? [], $rules, $idx);

        return $new;
    }

    /**
     * Merges the grammar with another.
     *
     * The resulting grammar will consist of all of the declarations and rules from both grammars. If both grammars
     * have rules for the same non-terminal, rules from the second grammar will take precedence.
     *
     * @param self $other The grammar instance to merge with.
     *
     * @return self The merged instance.
     */
    public function merge(self $other): self
    {
        $newDecls = array_merge($this->decls, $other->decls);

        $newRules = $this->rules;
        foreach ($other->rules as $nonTerminal => $rules) {
            $newRules[$nonTerminal] = isset($newRules[$nonTerminal])
                ? array_merge($newRules[$nonTerminal], $rules)
                : $rules;
        }

        return new self($newDecls, $newRules);
    }

    /**
     * Transforms the grammar instance into the equivalent Yacc grammar syntax.
     *
     * @return string A string containing the grammar code.
     */
    public function toString(): string
    {
        $declarations = array_map('strval', $this->decls);
        $declarationsStr = implode("\n", $declarations);

        $groups = [];
        foreach ($this->rules as $nonTerminal => $rules) {
            $rulesStrings = array_map('strval', $rules);
            $groupBody = implode("\n  | ", $rulesStrings);

            $groups[] = "{$nonTerminal}:\n    {$groupBody}\n;";
        }

        $rulesStr = implode("\n\n", $groups);

        return "{$declarationsStr}\n\n%%\n\n{$rulesStr}\n\n%%\n";
    }

    /**
     * Casts the grammar to a string.
     *
     * @see Grammar::toString()
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
