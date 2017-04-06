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
            if (is_file($this->getArgumentsFile($folder))) {
                $arguments = require $this->getArgumentsFile($folder);
                $this->executeSniffSubfolders($folder, $arguments);
                continue;
            }

            $this->executeSniff($folder);
        }
    }

    /**
     * Execute sniff using subfolders.
     *
     * @param SplFileInfo $folder
     * @param array $arguments
     * @return void
     */
    protected function executeSniffSubfolders(SplFileInfo $folder, array $arguments = [])
    {
        $finder = new Finder();
        $finder->in($folder->getRealPath());

        foreach ($arguments as $subFolder => $values) {
            $folderName = $folder->getRealPath() . DIRECTORY_SEPARATOR . $subFolder;
            $this->executeSniff(new SplFileInfo($folderName, $folderName, $folderName), $values);
        }
    }

    /**
     * Execute phpunit assertion for sniff based on $folder.
     *
     * @param SplFileInfo $folder
     * @param array $arguments
     * @return void
     */
    protected function executeSniff(SplFileInfo $folder, array $arguments = [])
    {
        $internalArguments = array_merge([
            'runtime-set' => 'mappingFile '
                . __DIR__ . DIRECTORY_SEPARATOR
                . 'Fixtures' . DIRECTORY_SEPARATOR
                . 'LegacyClassnames.php',
            'report' => 'json',
            'sniffs' => $this->getSniffByFolder($folder),
            'inputFile' => $folder->getRealPath() . DIRECTORY_SEPARATOR . 'InputFileForIssues.php',
        ], $arguments);

        if (isset($internalArguments['inputFileName'])) {
            $internalArguments['inputFile'] = $folder->getRealPath() . DIRECTORY_SEPARATOR . $internalArguments['inputFileName'];
            unset($internalArguments['inputFileName']);
        }

        $this->assertEquals(
            $this->getExpectedJsonOutput($folder),
            $this->getOutput($folder, $internalArguments)['output'],
            'Checking Sniff "' . $this->getSniffByFolder($folder) . '"'
                . ' did not produce expected output for input file '
                . $internalArguments['inputFile']
                . ' called: ' . $this->getPhpcsCall($folder, $internalArguments)
        );

        try {
            $internalArguments['report'] = 'diff';
            $this->assertEquals(
                $this->getExpectedDiffOutput($folder),
                $this->getOutput($folder, $internalArguments)['output'],
                'Fixing Sniff "' . $this->getSniffByFolder($folder) . '"'
                    . ' did not produce expected diff for input file '
                    . $internalArguments['inputFile']
                    . ' called: ' . $this->getPhpcsCall($folder, $internalArguments)
            );
        } catch (FileNotFoundException $e) {
            // Ok, ignore, we don't have an diff.
        }
    }

    /**
     * Get expected json output for comparison.
     *
     * @param SplFileInfo $folder
     * @return array
     */
    protected function getExpectedJsonOutput(SplFileInfo $folder)
    {
        $file = $folder->getPathname() . DIRECTORY_SEPARATOR . 'Expected.json';
        if (!is_file($file)) {
            throw new \Exception('Could not load file: ' . $file, 1491486050);
        }

        return json_decode(file_get_contents($file), true);
    }

    /**
     * Returns absolute file path to diff file containing expected output.
     *
     * @param SplFileInfo $folder
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function getExpectedDiffOutput(SplFileInfo $folder)
    {
        $file = $folder->getRealPath() . DIRECTORY_SEPARATOR . 'Expected.diff';
        if (!is_file($file)) {
            throw new FileNotFoundException('File does not exist.', 1491469621);
        }

        return file_get_contents($file);
    }

    /**
     * Returns PHPCS Sniff name for given folder.
     *
     * @param SplFileInfo $folder
     * @return string
     */
    protected function getSniffByFolder(SplFileInfo $folder)
    {
        $folderParts = array_filter(explode(DIRECTORY_SEPARATOR, $folder->getPathName()));
        $sniffNamePosition;

        foreach ($folderParts as $index => $folderPart) {
            if (strpos($folderPart, 'Sniff', 1) !== false) {
                $sniffNamePosition = $index;
                break;
            }
        }

        if ($sniffNamePosition === null) {
            throw new \Exception('Could not detect sniff name by folder: ' . var_export($folder, true), 1491485369);
        }

        return $folderParts[$sniffNamePosition - 3]
            . '.' . $folderParts[$sniffNamePosition - 1]
            . '.' . substr($folderParts[$sniffNamePosition], 0, -5);
    }

    /**
     * Get absolute file path to file containing further arguments.
     *
     * @param SplFileInfo $folder
     * @return string
     */
    protected function getArgumentsFile(SplFileInfo $folder)
    {
        return $folder->getRealPath() . DIRECTORY_SEPARATOR . 'Arguments.php';
    }

    /**
     * Build cli call for phpcs.
     *
     * @param SplFileInfo $folder
     * @param array $arguments
     * @return string
     */
    protected function getPhpcsCall(SplFileInfo $folder, array $arguments)
    {
        $bin = './vendor/bin/phpcs';
        $preparedArguments = [];

        foreach ($arguments as $argumentName => $argumentValue) {
            if ($argumentName === 'inputFile') {
                continue;
            }

            $prefix = "--$argumentName=";
            if (in_array($argumentName, ['runtime-set'])) {
                $prefix = "--$argumentName ";
            }

            $preparedArguments[] = "$prefix$argumentValue";
        }

        return $bin
            . ' ' . implode(' ', $preparedArguments)
            . ' ' . $arguments['inputFile']
            ;
    }

    /**
     * Executes phpcs for sniff based on $folder and returns the generated output.
     *
     * @param SplFileInfo $folder
     * @param array $arguments
     * @return array
     */
    protected function getOutput(SplFileInfo $folder, array $arguments)
    {
        $output = '';
        $returnValue;
        exec($this->getPhpcsCall($folder, $arguments), $output, $returnValue);

        if ($arguments['report'] === 'json') {
            $output = $this->prepareJsonOutput($output);
        } if ($arguments['report'] === 'diff') {
            $output = $this->prepareDiffOutput($output);
        }

        return [
            'output' => $output,
            'returnValue' => $returnValue,
        ];
    }

    /**
     * Prepare phpcs output for comparison.
     *
     * @param array $output
     * @return array
     */
    protected function prepareJsonOutput(array $output)
    {
        $preparedOutput = json_decode($output[0], true);

        if ($preparedOutput === null) {
            throw new \Exception('Output for phpcs was not valid json: ' . var_export($output, true), 1491485173);
        }

        foreach (array_keys($preparedOutput['files']) as $fileName) {
            $newKey = basename($fileName);
            $preparedOutput['files'][$newKey] = $preparedOutput['files'][$fileName];
            unset($preparedOutput['files'][$fileName]);
        }

        return $preparedOutput;
    }

    /**
     * Prepare phpcs output for comparison.
     *
     * @param array $output
     * @return string
     */
    protected function prepareDiffOutput(array $output)
    {
        return implode("\n", $output);
    }
}
