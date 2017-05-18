<?php
namespace Typo3Update\Feature;

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

use PHP_CodeSniffer_File as PhpCsFile;
use Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;

/**
 * Provides "feature" support for sniffs.
 */
trait FeaturesSupport
{
    use ExtendedPhpCsSupportTrait;

    /**
     * @return Features
     */
    protected function getFeatures()
    {
        return new Features($this);
    }

    /**
     * Processes all features for the sniff.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     * @param string $content
     */
    public function processFeatures(PhpCsFile $phpcsFile, $stackPtr, $content)
    {
        $content = $this->getStringContent($content);

        foreach ($this->getFeatures() as $featureClassName) {
            $feature = $this->createFeature($featureClassName);
            $feature->process($phpcsFile, $stackPtr, $content);
        }
    }

    /**
     * Create a new instance of the given feature.
     *
     * @param string $featureClassname
     * @return FeatureInterface
     */
    protected function createFeature($featureClassname)
    {
        return new $featureClassname($this);
    }
}
