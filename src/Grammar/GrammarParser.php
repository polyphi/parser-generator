<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Grammar;

use Polyphi\Parsers\Grammar;
use Polyphi\Parsers\Grammar\Nodes\Declaration;
use Polyphi\Parsers\Grammar\Nodes\LiteralToken;
use Polyphi\Parsers\Grammar\Nodes\NamedToken;
use Polyphi\Parsers\Grammar\Nodes\Rule;
use Polyphi\Parsers\Grammar\Nodes\Token;

class GrammarParser
{
    /** @var string[] */
    protected $input;

    /** @var int */
    protected $length;

    /** @var int */
    protected $idx;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->input = [];
        $this->length = 0;
        $this->idx = 0;
    }

    /**
     * Parses a string of Yacc grammar code into a {@link Grammar} object.
     *
     * @param string $code The Yacc grammar code.
     *
     * @return Grammar The parsed grammar.
     * @throws GrammarParseException If parsing failed.
     */
    public function parse(string $code): Grammar
    {
        $decls = [];
        $grammar = new Grammar([], []);
        $this->input = str_split($code);
        $this->length = count($this->input);
        $this->idx = 0;
        $state = 0;

        while ($this->idx < $this->length) {
            $char = $this->skipWs();

            switch ($state) {
                // Parse declarations
                case 0:
                    if ($char !== '%') {
                        $this->throwException('Expected declaration');
                    }

                    $this->idx++;

                    if ($this->getChar() === '%') {
                        // Handle "%%" - Move onto rules
                        $state++;
                        $this->idx++;
                    } else {
                        $decls[] = $this->parseDeclaration();
                    }

                    break;

                // Parse rules
                case 1:
                    // Handle "%%" or end of input - stop parsing
                    if (($char === '%' && $this->getChar(1) === '%') || $this->idx >= $this->length) {
                        // Skip the "%%"
                        $this->idx += 2;
                        // Allow whitespace after end-of-input marker
                        $this->skipWs();
                        $state++;
                    } else {
                        [$nonTerminal, $ruleList] = $this->parseRuleList();
                        $grammar = $grammar->withAddedRules($nonTerminal, $ruleList);
                    }

                    break;

                // Reached end of input, but there are still characters left to read
                case 2:
                    $this->throwException("Unexpected \"$char\" after end-of-input marker");
            }
        }

        return $grammar->withDeclarations($decls);
    }

    /**
     * Parses a declaration.
     *
     * @return Declaration The parsed declaration.
     * @throws GrammarParseException If parsing failed.
     */
    protected function parseDeclaration(): Declaration
    {
        $type = $this->parseIdentifier();
        if (empty($type)) {
            $this->throwException('Expected declaration type');
        }

        $this->skipWs();

        $tokens = $this->parseTokenList();

        return new Declaration($type, $tokens);
    }

    /**
     * Parses a list of rules.
     *
     * @see    parseRule()
     *
     * @return array{0: string, 1: Rule[]} An array parsed rules.
     * @throws GrammarParseException If parsing failed.
     */
    protected function parseRuleList(): array
    {
        $name = $this->parseIdentifier();
        if (empty($name)) {
            $this->throwException('Expected non-terminal name');
        }

        $char = $this->skipWs();

        if ($char !== ':') {
            $this->throwException("Expected \":\" after \"$name\"");
        }
        $this->idx++;

        $rules = [];
        do {
            $this->skipWs();
            $rules[] = $this->parseRule();
        } while ($this->skipWs() === '|' && ++$this->idx);

        $char = $this->skipWs();

        if ($char !== ';') {
            $this->throwException('Expected ";"');
        }

        // Skip ";"
        $this->idx++;

        return [$name, $rules];
    }

    /**
     * Parses a single rule.
     *
     * @see parseTokenList()
     * @see parseCode()
     *
     * @return Rule The parsed rule.
     * @throws GrammarParseException If parsing failed.
     */
    protected function parseRule(): Rule
    {
        $this->skipWs();

        $tokens = $this->parseTokenList();
        $code = $this->parseCode();

        return new Rule($tokens, $code);
    }

    /**
     * Parses code that follows a rule.
     *
     * @return string The parsed code, without the braces.
     * @throws GrammarParseException If parsing failed.
     */
    protected function parseCode(): string
    {
        $char = $this->skipWs();

        if ($char !== '{') {
            return '';
        }

        // skip "{"
        $this->idx++;

        $numBraces = 0;
        $code = static::readWhile(true, function (string $chr) use (&$numBraces) {
            if ($chr === '{') {
                $numBraces++;
            } elseif ($chr === '}') {
                $numBraces--;
            }

            return $numBraces >= 0;
        });

        if ($this->getChar() !== '}') {
            $this->throwException('Unterminated code');
        }

        // skip "}"
        $this->idx++;

        return trim($code);
    }

    /**
     * Parses a list of tokens.
     *
     * @see parseLiteralToken()
     * @see parseIdentifier()
     *
     * @return Token[] The list of parsed tokens.
     * @throws GrammarParseException If parsing failed.
     */
    protected function parseTokenList(): array
    {
        $tokens = [];

        do {
            $this->skipWs();

            $char = $this->getChar();
            if ($char === '\'') {
                $tokens[] = $this->parseLiteralToken();
            } elseif ($this->isIdentifierChar($char)) {
                $tokens[] = new NamedToken($this->parseIdentifier());
            } else {
                break;
            }
        } while (true);

        return $tokens;
    }

    /**
     * Parses a literal token.
     *
     * @return LiteralToken The parsed literal token.
     *
     * @throws GrammarParseException If parsing failed.
     */
    protected function parseLiteralToken(): LiteralToken
    {
        if ($this->getChar() !== '\'') {
            $this->throwException('Expected "\'"');
        }

        $this->idx++;

        $escaped = false;
        $literal = $this->readWhile(true, function (string $char) use (&$escaped): bool {
            switch ($char) {
                case '\\':
                    $escaped = !$escaped;
                    break;
                case '\'':
                    if (!$escaped) {
                        return false;
                    }

                    $escaped = false;
                    break;
            }

            return true;
        });

        if ($this->getChar() !== '\'') {
            $this->throwException('Expected "\'"');
        }

        $this->idx++;

        return new LiteralToken($literal);
    }

    /**
     * Parses an identifier.
     *
     * @todo Check if Yacc identifiers can start with digits.
     *
     * @see  isIdentifierChar()
     *
     * @return string The parsed identifier, which may be empty.
     */
    protected function parseIdentifier(): string
    {
        return $this->readWhile(true, [$this, 'isIdentifierChar']);
    }

    /**
     * Checks if a character is a valid identifier character.
     *
     * @param string $char
     *
     * @return bool
     */
    protected function isIdentifierChar(string $char): bool
    {
        return ctype_alnum($char) || $char === '_';
    }

    /**
     * Reads characters until a criteria is met.
     *
     * @param bool     $is    If true, reading will stop when the $when function returns false. If false, reading will
     *                        stop when the $when function returns true.
     * @param callable $while A function that accepts a character and returns a boolean. The function should return the
     *                        the value of $is in order to keep reading characters, and the negated value of $is to
     *                        stop reading.
     *
     * @return string The string of characters that were read before the $match function rejected a character or the
     *                end of the input is reached. The rejected last character is not included in this string.
     */
    protected function readWhile(bool $is, callable $while): string
    {
        $match = '';

        while ($this->idx < $this->length) {
            $char = $this->input[$this->idx];
            if ($while($char) === $is) {
                $match .= $char;
                $this->idx++;
            } else {
                break;
            }
        }

        return $match;
    }

    /**
     * Retrieves the current character.
     *
     * @param int $offset Optional offset of characters. If given, the character that follows that current character
     *                    by $offset indices will be returned. Default is zero.
     *
     * @return string The current character. An empty string will be returned if the current index plus the $offset
     *                is out of bounds.
     */
    protected function getChar(int $offset = 0): string
    {
        return $this->input[$this->idx + $offset] ?? '';
    }

    /**
     * Skips over whitespace (including comments).
     *
     * @return string The next non-whitespace character.
     */
    protected function skipWs(): string
    {
        while (true) {
            $chr = $this->getChar();

            // Skip white space
            if (ctype_space($chr)) {
                static::readWhile(true, 'ctype_space');

                continue;
            }

            // Skip comments
            if ($chr === '/') {
                $next = $this->getChar(1);
                $multiLine = ($next === '*');
                $singleLine = ($next === '/');

                if ($multiLine || $singleLine) {
                    // Skip "/*" or "//"
                    $this->idx += 2;

                    if ($multiLine) {
                        $this->readWhile(true, function (string $chr): bool {
                            return !($chr === '*' && $this->getChar(1) === '/');
                        });

                        // Skip last "*/"
                        $this->idx += 2;
                    } else {
                        $this->readWhile(true, function (string $chr): bool {
                            return $chr === "\n";
                        });
                    }

                    continue;
                }
            }

            break;
        }

        return $this->getChar();
    }

    /**
     * Throws a parse exception.
     *
     * @param string $message The message for the exception.
     *
     * @return never-returns
     * @throws GrammarParseException
     */
    protected function throwException(string $message): void
    {
        $line = 1;
        $col = 1;
        for ($i = 0; $i < $this->idx; ++$i) {
            if ($this->input[$i] === "\n") {
                $line++;
                $col = 1;
            } else {
                $col++;
            }
        }

        throw new GrammarParseException("$message, on line $line at column $col");
    }
}
