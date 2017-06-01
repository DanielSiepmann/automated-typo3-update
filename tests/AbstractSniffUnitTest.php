<?php
namespace Typo3Update\Tests;

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

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest as PhpCsAbstractSniffUnitTest;
use Symfony\Component\Finder\Finder;

use PHP_CodeSniffer\Tests\Standards\AllSniffs;

abstract class AbstractSniffUnitTest extends PhpCsAbstractSniffUnitTest
{
    protected function setUp()
    {
        // Trigger include for bootstrapping.
        // TODO: Move to bootstrap file.
        class_exists(AllSniffs::class);
        $GLOBALS['PHP_CODESNIFFER_SNIFF_CODES'] = [];
        $GLOBALS['PHP_CODESNIFFER_FIXABLE_CODES'] = [];

        $class = get_class($this);
        $GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'][$class] = implode(
            DIRECTORY_SEPARATOR,
            [__DIR__, '..', 'src', 'Standards', 'Typo3Update', 'Sniffs', '']
        );
        $GLOBALS['PHP_CODESNIFFER_TEST_DIRS'][$class] = $this->getFixturePath();

        parent::setUp();
    }

    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
     */
    protected function getErrorList()
    {
        return $this->getExpectdOutput()['ERROR'];
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array(int => int)
     */
    protected function getWarningList()
    {
        return $this->getExpectdOutput()['WARNING'];
    }

    protected function getFixturePath()
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [__DIR__, 'Fixtures', 'Standards', 'Typo3Update', 'Sniffs', '']
        );
    }

    protected function getExpectdOutput()
    {
        $list = [
            'WARNING' => [],
            'ERROR' => [],
        ];

        $file = $this->getFixturePath() . implode(
            DIRECTORY_SEPARATOR,
            array_slice(explode('\\', str_replace('Sniff', '', get_class($this))), 2)
        ) . 'Expected.json';

        if (!is_file($file)) {
            throw new \Exception('Could not load file: ' . $file, 1491486050);
        }

        $content = json_decode(file_get_contents($file), true);

        foreach ($content['files']['InputFileForIssues.php']['messages'] as $message) {
            $type = $message['type'];

            if (!isset($list[$type][$message['line']])) {
                $list[$type][$message['line']] = 1;
                continue;
            }

            ++$list[$type][$message['line']];
        }

        return $list;
    }
}
