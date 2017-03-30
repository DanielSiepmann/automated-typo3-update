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
    public function tokenizeString($string, $eolChar='\n')
    {
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** START TypoScript TOKENIZING ***" . PHP_EOL;
        }

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenizeString($string);
        $finalTokens = [];
        $numTokens = count($tokens);

        /**
         * Each token within the stack contains information about itself:
         *
         * <code>
         *   array(
         *    'code'       => 301,       // the token type code (see token_get_all())
         *    'content'    => 'if',      // the token content
         *    'type'       => 'T_IF',    // the token name
         *    'line'       => 56,        // the line number when the token is located
         *    'column'     => 12,        // the column in the line where this token
         *                               // starts (starts from 1)
         *    'level'      => 2          // the depth a token is within the scopes open
         *    'conditions' => array(     // a list of scope condition token
         *                               // positions => codes that
         *                     2 => 50,  // opened the scopes that this token exists
         *                     9 => 353, // in (see conditional tokens section below)
         *                    ),
         *   );
         * </code>
         *
         * <b>Conditional Tokens</b>
         *
         * In addition to the standard token fields, conditions contain information to
         * determine where their scope begins and ends:
         *
         * <code>
         *   array(
         *    'scope_condition' => 38, // the token position of the condition
         *    'scope_opener'    => 41, // the token position that started the scope
         *    'scope_closer'    => 70, // the token position that ended the scope
         *   );
         * </code>
         *
         * The condition, the scope opener and the scope closer each contain this
         * information.
         *
         * <b>Parenthesis Tokens</b>
         *
         * Each parenthesis token (T_OPEN_PARENTHESIS and T_CLOSE_PARENTHESIS) has a
         * reference to their opening and closing parenthesis, one being itself, the
         * other being its opposite.
         *
         * <code>
         *   array(
         *    'parenthesis_opener' => 34,
         *    'parenthesis_closer' => 40,
         *   );
         * </code>
         *
         * Some tokens can "own" a set of parenthesis. For example a T_FUNCTION token
         * has parenthesis around its argument list. These tokens also have the
         * parenthesis_opener and and parenthesis_closer indices. Not all parenthesis
         * have owners, for example parenthesis used for arithmetic operations and
         * function calls. The parenthesis tokens that have an owner have the following
         * auxiliary array indices.
         *
         * <code>
         *   array(
         *    'parenthesis_opener' => 34,
         *    'parenthesis_closer' => 40,
         *    'parenthesis_owner'  => 33,
         *   );
         * </code>
         *
         * Each token within a set of parenthesis also has an array index
         * 'nested_parenthesis' which is an array of the
         * left parenthesis => right parenthesis token positions.
         *
         * <code>
         *   'nested_parenthesis' => array(
         *                             12 => 15
         *                             11 => 14
         *                            );
         * </code>
         */

        $level = 0;
        for ($stackPtr = 0; $stackPtr < $numTokens; $stackPtr++) {
            $token = $tokens[$stackPtr];
            $finalTokens[$stackPtr] = [
                'code' => $this->mapTypeToCode($token),
                'type' => $token->getType(),
                'line' => $token->getLine(),
                'column' => 0,
                'content' => $token->getValue(),
                'level' => $level,
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

    /**
     * Returns mapped PHP code equivalent for token.
     *
     * @param Token $token
     * @return int
     */
    protected function mapTypeToCode(TokenInterface $token)
    {
        $tokenType = $token->getType();
        $mapping = [
            TokenInterface::TYPE_COMMENT_ONELINE => T_COMMENT,
        ];

        if (!isset($mapping[$tokenType])) {
            // TODO: Throw exception?!
        }
        return $mapping[$tokenType];
    }
}
