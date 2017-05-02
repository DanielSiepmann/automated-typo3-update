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
use PHP_CodeSniffer_Tokens as PhpCsTokens;
use Typo3Update\Options;
use Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;
use Typo3Update\Sniffs\Removed\AbstractGenericPhpUsage;

class Typo3Update_Sniffs_Removed_GenericHookSniff extends AbstractGenericPhpUsage
{
    use ExtendedPhpCsSupportTrait;
    public function register()
    {
        return PhpCsTokens::$stringTokens;
    }

    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedHookConfigFiles();
    }

    protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $firstPart = $this->getStringContent($tokens[$stackPtr]['content']);
        $secondPart = $this->getStringContent($tokens[$phpcsFile->findNext(PhpCsTokens::$stringTokens, $stackPtr + 1)]['content']);

        $lookup = $firstPart . '->' . $secondPart;

        if ($this->configured->isRemoved($lookup) === false) {
            return [];
        }

        return [$this->configured->getRemoved($lookup)];
    }

    protected function getIdentifier(array $config)
    {
        $search = ['/', '.'];
        $replace = ['-'];

        return str_replace($search, $replace, $config['fqcn']) . $config['name'];
    }

    protected function getOldUsage(array $config)
    {
        return '["' . $config['fqcn'] . '"]["' . $config['name'] . '"] = ...';
    }
}
