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

use PHP_CodeSniffer_Sniff as PhpCsSniff;
use Typo3Update\Options;

/**
 * Contains all configured features for a single sniff.
 */
class Features implements \Iterator
{
    /**
     * Internal array position for \Iterator implementation.
     * @var int
     */
    protected $index = 0;

    /**
     * Internal array
     * @var array
     */
    protected $features = [];

    /**
     * @param PhpCsSniff $sniff The sniff to collect features for.
     */
    public function __construct(PhpCsSniff $sniff)
    {
        foreach (Options::getFeaturesConfiguration() as $featureName => $sniffs) {
            if (in_array(get_class($sniff), $sniffs)) {
                $this->addFeature($featureName);
            }
        }
    }

    /**
     * Add the given feature.
     *
     * @param string $featureName
     * @return void
     */
    protected function addFeature($featureName)
    {
        if (!class_implements($featureName, FeatureInterface::class)) {
            throw new \Exception(
                'Configured Feature "' . $featureName . '" does not implement "' . FeatureInterface::class . '".',
                1493115488
            );
        }

        $this->features[] = $featureName;
    }

    // implement Iterator interface:
    public function current()
    {
        return $this->features[$this->index];
    }
    public function key()
    {
        return $this->index;
    }
    public function next()
    {
        ++$this->index;
    }
    public function rewind()
    {
        $this->index = 0;
    }
    public function valid()
    {
        return isset($this->features[$this->index]);
    }
}
