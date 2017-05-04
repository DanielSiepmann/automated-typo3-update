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

use PHPUnit\Framework\TestCase;
use PHP_CodeSniffer as PhpCs;
use PHP_CodeSniffer_File as PhpCsFile;

class FluidTest extends TestCase
{
    /**
     * @test
     */
    public function callingTokenizerWorksAsExpected()
    {
        // Initialize constants, etc.
        new PhpCs();

        $subject = new \PHP_CodeSniffer_Tokenizers_FLUID();
        $resultFile = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            'Fixtures',
            'CodeSniffer',
            'Tokenizers',
            'Fluid',
            'expected.php',
        ]);
        $testFile = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            'Fixtures',
            'CodeSniffer',
            'Tokenizers',
            'Fluid',
            'example.html',
        ]);

        $this->assertEquals(
            require $resultFile,
            PhpCsFile::tokenizeString(file_get_contents($testFile), $subject, "\n"),
            'Did not get expected tokens.'
        );
    }
}
