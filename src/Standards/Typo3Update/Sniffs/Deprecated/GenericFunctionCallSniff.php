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

use PHP_CodeSniffer_File as PhpCsFile;
use Typo3Update\Sniffs\Deprecated\AbstractFunctionCall;

/**
 */
class Typo3Update_Sniffs_Deprecated_GenericFunctionCallSniff extends AbstractFunctionCall
{
    protected $deprecatedFunctions = [
        'loadTCA' => [
            'oldFunctionCall' => '\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA()',
            'newFunctionCall' => null,
            'docsUrl' => 'https://docs.typo3.org/typo3cms/extensions/core/Changelog/7.0/Breaking-61785-LoadTcaFunctionRemoved.html',
        ],
    ];

    protected function getFunctionNames()
    {
        return array_keys($this->deprecatedFunctions);
    }

    protected function getOldFunctionCall($functionName)
    {
        return $this->deprecatedFunctions[$functionName]['oldFunctionCall'];
    }

    protected function getNewFunctionCall($functionName)
    {
        $newCall = $this->deprecatedFunctions[$functionName]['newFunctionCall'];
        if ($newCall !== null) {
            return $newCall;
        }
        return 'There is no replacement, just remove call';
    }

    protected function getDocsUrl($functionName)
    {
        return $this->deprecatedFunctions[$functionName]['docsUrl'];
    }
}
