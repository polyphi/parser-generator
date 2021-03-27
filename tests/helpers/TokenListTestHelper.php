<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Helpers;

use Polyphi\Parsers\Grammar\Token;

trait TokenListTestHelper
{
    /**
     * Utility function to create a list of tokens from a list of strings
     *
     * @param string[] $strings The strings that the tokens reduce to.
     *
     * @return Token[]
     */
    protected function tokenList(array $strings): array
    {
        return array_map(function (string $string): Token {
            return $this->createConfiguredMock(Token::class, ['toString' => $string]);
        }, $strings);
    }
}
