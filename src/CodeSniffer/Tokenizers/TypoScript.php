<?php

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

/**
 * Tokenizes a string of TypoScript.
 */
class PHP_CodeSniffer_Tokenizers_TYPOSCRIPT
{
    /**
     * If TRUE, files that appear to be minified will not be processed.
     *
     * @var boolean
     */
    public $skipMinified = false;

    /**
     * Creates an array of tokens when given some TypoScript code.
     *
     * @param string $string  The string to tokenize.
     * @param string $eolChar The EOL character to use for splitting strings.
     *
     * @return array
     */
    public function tokenizeString($string, $eolChar = '\n')
    {
        $finalTokens = [];
        $tokenizer = new Tokenizer($eolChar, false);

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
     * @param array  $tokens  The array of tokens to process.
     * @param string $eolChar The EOL character to use for splitting strings.
     *
     * @return void
     */
    public function processAdditional(&$tokens, $eolChar)
    {
        return;
    }
}
