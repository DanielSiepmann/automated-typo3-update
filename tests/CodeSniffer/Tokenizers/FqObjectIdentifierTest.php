<?php

namespace Typo3Update\Tests\CodeSniffer\Tokenizers;

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
use PHPUnit\Framework\TestCase;
use Typo3Update\CodeSniffer\Tokenizers\FQObjectIdentifier;

class FqObjectIdentifierTest extends TestCase
{
    /**
     * @test
     */
    public function addingPathSegmentAddsFqToNewToken()
    {
        $initialToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'plugin.tx_example',
        ];
        $lastToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'settings',
        ];
        $expectedResult = $lastToken;
        $expectedResult['fqObjectIdentifier'] = 'plugin.tx_example.settings';

        $identifier = new FqObjectIdentifier();
        $identifier->addPathSegment($initialToken);
        $identifier->handleOpeningBrace();
        $identifier->addPathSegment($lastToken);

        $this->assertEquals(
            $expectedResult,
            $lastToken,
            'Adding path segment does not add FQObjectIdentifier to token.'
        );
    }

    /**
     * @test
     */
    public function addingPathSegment2ndTimeAddsFqToNewToken()
    {
        $initialToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'plugin.tx_example',
        ];
        $firstToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'settings',
        ];
        $lastToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'someSetting',
        ];
        $expectedResult = $lastToken;
        $expectedResult['fqObjectIdentifier'] = 'plugin.tx_example.settings.someSetting';

        $identifier = new FqObjectIdentifier();
        $identifier->addPathSegment($initialToken);
        $identifier->handleOpeningBrace();
        $identifier->addPathSegment($firstToken);
        $identifier->handleOpeningBrace();
        $identifier->addPathSegment($lastToken);

        $this->assertEquals(
            $expectedResult,
            $lastToken,
            'Adding path segment does not add FQObjectIdentifier to token on 2nd call.'
        );
    }

    /**
     * @test
     */
    public function openingAndClosingBracesWillAdjustPath()
    {
        $initialToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'plugin.tx_example',
        ];
        $firstToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'settings',
        ];
        $secondToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'someSetting',
        ];
        $lastToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'view',
        ];
        $expectedResult = $lastToken;
        $expectedResult['fqObjectIdentifier'] = 'plugin.tx_example.view';

        $identifier = new FqObjectIdentifier();
        $identifier->addPathSegment($initialToken);
        $identifier->handleOpeningBrace();
        $identifier->addPathSegment($firstToken);
        $identifier->handleOpeningBrace();
        $identifier->addPathSegment($secondToken);
        $identifier->handleClosingBrace();
        $identifier->addPathSegment($lastToken);

        $this->assertEquals(
            $expectedResult,
            $lastToken,
            'Curly braces do not modify path as expected.'
        );
    }

    /**
     * @test
     */
    public function addingPathSegmentAfterAnotherResetsPath()
    {
        $initialToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'plugin.tx_example.settings.someThing',
        ];
        $lastToken = [
            'type' => TokenInterface::TYPE_OBJECT_IDENTIFIER,
            'content' => 'plugin.tx_example.settings.anotherOne',
        ];
        $expectedResult = $lastToken;
        $expectedResult['fqObjectIdentifier'] = $expectedResult['content'];

        $identifier = new FqObjectIdentifier();
        $identifier->addPathSegment($initialToken);
        $identifier->addPathSegment($lastToken);

        $this->assertEquals(
            $expectedResult,
            $lastToken,
            'Adding path segment without braces in between resets.'
        );
    }
}
