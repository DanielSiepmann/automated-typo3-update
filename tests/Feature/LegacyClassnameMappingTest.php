<?php
namespace Typo3Update\Tests\Feature;

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
use PHP_CodeSniffer\Config;
use Typo3Update\Feature\LegacyClassnameMapping;
use org\bovigo\vfs\vfsStream;

class LegacyClassnameMappingTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $fileSystem;

    /**
     * @var LegacyClassnameMapping
     */
    protected $subject;

    /**
     * @param string $fileName
     * @return string
     */
    protected function getFixturePath($fileName)
    {
        return implode(DIRECTORY_SEPARATOR, [
            __DIR__, '..', 'Fixtures', 'Standards','Typo3Update',
            'Feature', 'LegacyClassnameMapping', $fileName,
        ]);
    }

    public function setUp()
    {
        $this->fileSystem = vfsStream::setup('root', null, [
            'LegacyClassnames.php' => file_get_contents($this->getFixturePath('MappingContent.php')),
        ]);
        // Config::setConfigData('mappingFile', vfsStream::url('root/LegacyClassnames.php'));

        $this->subject = LegacyClassnameMapping::getInstance();
    }

    /**
     * @test
     */
    public function inCaseSensitivityLookupWorks()
    {
        $this->assertFalse(
            $this->subject->isLegacyClassname('tx_extbase_domain_model_backenduser'),
            'Classname was returned to be legacy but should not due to lowercase version and case sensitivity.'
        );
        $this->assertFalse(
            $this->subject->isLegacyClassname('Tx_Extbase_Domain_Model_Backenduser'),
            'Classname was returned to be legacy but should not due to lowercase version and case sensitivity.'
        );
        $this->assertTrue(
            $this->subject->isCaseInsensitiveLegacyClassname('Tx_Extbase_Domain_Model_Backenduser'),
            'Classname was not returned to be legacy but should due to case insensitivity.'
        );
    }

    /**
     * @test
     */
    public function weCanRetrieveNewClassname()
    {
        $this->assertSame(
            'TYPO3\CMS\Extbase\Command\HelpCommandController',
            $this->subject->getNewClassname('tx_extbase_command_helpcommandcontroller'),
            'New class name could not be fetched for lower cased version.'
        );
        $this->assertSame(
            'TYPO3\CMS\Extbase\Command\HelpCommandController',
            $this->subject->getNewClassname('Tx_Extbase_Command_HelpCommandController'),
            'New class name could not be fetched.'
        );
    }

    /**
     * @test
     */
    public function nothingWillBePersistedUntilWeAddSomething()
    {
        $this->subject->persistMappings();
        $this->assertSame(
            file_get_contents($this->getFixturePath('MappingContent.php')),
            file_get_contents(vfsStream::url('root/LegacyClassnames.php')),
            'File content should not be changed.'
        );

        $this->subject->addLegacyClassname('Tx_ExtName_Controller_ExampleController', '\\Vendor\\ExtName\\Controller\\ExampleController');
        $this->subject->persistMappings();

        $this->assertSame(
            file_get_contents($this->getFixturePath('ExpectedMappingContent.php')),
            file_get_contents(vfsStream::url('root/LegacyClassnames.php')),
            'File content is not changed as expected.'
        );
    }

    /**
     * @test
     * @runInSeparateProcess Because of file operations.
     */
    public function addingLegacyClassnamesWillAdjustLookupAndBePersisted()
    {
        $this->assertFalse(
            $this->subject->isLegacyClassname('Tx_ExtName_Controller_ExampleController'),
            'Classname is not configured but returned to be legacy.'
        );

        $this->subject->addLegacyClassname('Tx_ExtName_Controller_ExampleController', '\\Vendor\\ExtName\\Controller\\ExampleController');

        $this->assertTrue(
            $this->subject->isLegacyClassname('Tx_ExtName_Controller_ExampleController'),
            'Classname is configured but not returned to be legacy.'
        );
    }

    /**
     * @test
     * @runInSeparateProcess Because of file operations.
     */
    public function weCanRetrieveNewClassnameAddedBefore()
    {
        $this->subject->addLegacyClassname('Tx_ExtName_Controller_ExampleController', '\\Vendor\\ExtName\\Controller\\ExampleController');
        $this->assertSame(
            '\\Vendor\\ExtName\\Controller\\ExampleController',
            $this->subject->getNewClassname('Tx_ExtName_Controller_ExampleController'),
            'New class name could not be fetched.'
        );
    }

    public function tearDown()
    {
        unset($this->subject);
        unset($this->fileSystem);
    }
}
