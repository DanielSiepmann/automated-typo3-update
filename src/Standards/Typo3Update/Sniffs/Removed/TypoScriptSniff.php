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
use PHP_CodeSniffer_File as PhpCsFile;
use Typo3Update\Options;
use Typo3Update\Sniffs\Removed\AbstractGenericUsage;

class Typo3Update_Sniffs_Removed_TypoScriptSniff extends AbstractGenericUsage
{
    /**
     * Register sniff only for TypoScript.
     * @var array<string>
     */
    public $supportedTokenizers = [
        'TYPOSCRIPT',
    ];

    public function register()
    {
        return [
            TokenInterface::TYPE_OBJECT_CONSTRUCTOR,
            TokenInterface::TYPE_OBJECT_IDENTIFIER,
        ];
    }

    protected function prepareStructure(array $typo3Versions)
    {
        $newStructure = [];
        foreach ($typo3Versions as $typo3Version => $removals) {
            foreach ($removals as $removed => $config) {
                $config['type'] = TokenInterface::TYPE_OBJECT_IDENTIFIER;
                // If starting with new, it's a constructor, meaning content object or other Object.
                if (strtolower(substr($removed, 0, 4)) === 'new ') {
                    $config['type'] = TokenInterface::TYPE_OBJECT_CONSTRUCTOR;
                    $removed = substr($removed, 4);
                }

                $config['name'] = $removed;
                $config['identifier'] = str_replace('.', '-', $removed);
                $config['versionRemoved'] = $typo3Version;
                $config['oldUsage'] = $removed;

                $newStructure[$removed] = $config;
            }
        }

        return $newStructure;
    }

    protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $objectIdentifier = $token['content'];

        if (!$this->configured->isRemoved($objectIdentifier)) {
            return [];
        }

        $removed = $this->configured->getRemoved($objectIdentifier);
        if ($token['type'] === $removed['type']) {
            return [$removed];
        }

        return [];
    }

    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedTypoScriptConfigFiles();
    }
}
