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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Will test all sniffs where fixtures are available.
 *
 * To add a test, just create the necessary fixture folder structure with files.
 */
class SniffsTest extends TestCase
{
    /**
     * Get all fixtures for sniffs.
     *
     * Execute each sniff based on found fixtures and compare result.
     *
     * @test
     */
    public function sniffs()
    {
        $finder = new Finder();
        $finder->in(
            __DIR__
            . DIRECTORY_SEPARATOR . 'Fixtures'
            . DIRECTORY_SEPARATOR . 'Standards'
            . DIRECTORY_SEPARATOR . 'Typo3Update'
            . DIRECTORY_SEPARATOR . 'Sniffs'
        );

        foreach ($finder->directories()->name('*Sniff') as $folder) {
            $this->executeSniff($folder);
        }
    }

    /**
     * Execute phpunit assertion for sniff based on $folder.
     *
     * @param SplFileInfo $folder
     * @return void
     */
    protected function executeSniff(SplFileInfo $folder)
    {
        $this->assertEquals(
            $this->getExpectedOutput($folder),
            $this->getOutput($folder)['output'],
            'Sniff ' . $this->getSniffByFolder($folder)
                . ' did not produce expected output for input file '
                . $this->getExpectedOutputFile($folder)
        );
    }

    /**
     * Returns PHPCS Sniff name for given folder.
     *
     * @param SplFileInfo $folder
     * @return string
     */
    protected function getSniffByFolder(SplFileInfo $folder)
    {
        $folderParts = explode(DIRECTORY_SEPARATOR, $folder->getPath());
        return array_slice($folderParts, -3)[0]
            . '.' .  array_slice($folderParts, -1)[0]
            . '.' . substr($folder->getFilename(), 0, -5);
    }

    /**
     * Returns absolute file path to json file containing expected output.
     *
     * @param SplFileInfo $folder
     * @return string
     */
    protected function getExpectedOutputFile(SplFileInfo $folder)
    {
        return $folder->getRealPath() . DIRECTORY_SEPARATOR . 'ExpectedOutputForIssues.json';
    }

    /**
     * Get expected output for comparison.
     *
     * @param SplFileInfo $folder
     * @return array
     */
    protected function getExpectedOutput(SplFileInfo $folder)
    {
        return json_decode(
            file_get_contents($this->getExpectedOutputFile($folder)),
            true
        );
    }

    /**
     * Executes phpcs for sniff based on $folder and returns the generated output.
     *
     * @param SplFileInfo $folder
     * @return array
     */
    protected function getOutput(SplFileInfo $folder)
    {
        $bin = './vendor/bin/phpcs';
        $arguments = '--sniffs=' . $this->getSniffByFolder($folder)
            . ' --report=json'
            . ' '
            . $folder->getRealPath() . DIRECTORY_SEPARATOR . 'InputFileForIssues.php'
        ;
        $output = '';
        $returnValue;

        exec($bin . ' ' . $arguments, $output, $returnValue);

        return [
            'output' => $this->prepareOutput($output),
            'returnValue' => $returnValue,
        ];
    }

    /**
     * Prepare phpcs output for comparison.
     *
     * @param array $output
     * @return array
     */
    protected function prepareOutput(array $output)
    {
        $preparedOutput = json_decode($output[0], true);

        foreach (array_keys($preparedOutput['files']) as $fileName) {
            $newKey = basename($fileName);
            $preparedOutput['files'][$newKey] = $preparedOutput['files'][$fileName];
            unset($preparedOutput['files'][$fileName]);
        }

        return $preparedOutput;
    }
}
