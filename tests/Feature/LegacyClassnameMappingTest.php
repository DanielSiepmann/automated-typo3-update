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

        $GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'] = [
            'mappingFile' => vfsStream::url('root/LegacyClassnames.php'),
        ];

        $this->subject = LegacyClassnameMapping::getInstance();
    }

    /**
     * @test
     */
    public function insensitivityLookupWorks()
    {
        $this->assertFalse(
            $this->subject->isLegacyClassname('Tx_About_Controller_Aboutcontroller', false),
            'Classname was returned to be legacy but should not due to lowercase version and case sensitivity.'
        );
        $this->assertTrue(
            $this->subject->isLegacyClassname('Tx_About_Controller_Aboutcontroller'),
            'Classname was not returned to be legacy but should due to case insensitivity.'
        );
    }

    /**
     * @test
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

        $this->subject->persistMappings();

        $this->assertSame(
            file_get_contents($this->getFixturePath('ExpectedMappingContent.php')),
            file_get_contents(vfsStream::url('root/LegacyClassnames.php')),
            'Persisted mappings are not as expected.'
        );
    }

    public function tearDown()
    {
        unset($this->subject);
        unset($this->fileSystem);
    }
}
