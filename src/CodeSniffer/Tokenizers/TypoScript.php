<?php

namespace PHP_CodeSniffer\Tokenizers;

/*
 * Copyright (C) 2017  Daniel Siepmann <coding@daniel-siepmann.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use PHP_CodeSniffer\Tokenizers\Tokenizer as AbstractTokenizer;
use Typo3Update\CodeSniffer\Tokenizers\FQObjectIdentifier;

/**
 * Tokenizes a string of TypoScript.
 */
class TYPOSCRIPT extends AbstractTokenizer
{
    /**
     * Creates an array of tokens when given some TypoScript code.
     *
     * @param string $string  The string to tokenize.
     *
     * @return array
     */
    public function tokenize($string)
    {
        $finalTokens = [];
        $tokenizer = new Tokenizer($this->eolChar);

        foreach ($tokenizer->tokenizeString($string) as $stackPtr => $token) {
            $finalTokens[$stackPtr] = [
                'code' => $token->getType(),
                'type' => $token->getType(),
                'line' => $token->getLine(),
                'content' => $token->getValue(),
            ];
        }

        return $finalTokens;
    }

    /**
     * Allow the tokenizer to do additional processing if required.
     *
     * @return void
     */
    public function processAdditional()
    {
        $this->addFQObjectIdentifiers();
    }

    /**
     * Add fully qualified object identifier to all object identifiers.
     */
    protected function addFQObjectIdentifiers()
    {
        $fqObjectIdentifier = new FQObjectIdentifier();

        foreach ($this->tokens as &$token) {
            if ($token['type'] === TokenInterface::TYPE_OBJECT_IDENTIFIER) {
                $fqObjectIdentifier->addPathSegment($token);
                continue;
            }
            if ($token['type'] === TokenInterface::TYPE_BRACE_OPEN) {
                $fqObjectIdentifier->handleOpeningBrace();
                continue;
            }
            if ($token['type'] === TokenInterface::TYPE_BRACE_CLOSE) {
                $fqObjectIdentifier->handleClosingBrace();
                continue;
            }
        }
    }
}
