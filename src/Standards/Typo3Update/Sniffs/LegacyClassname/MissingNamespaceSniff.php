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
use PHP_CodeSniffer_Sniff as PhpCsSniff;
use Typo3Update\Feature\LegacyClassnameMapping;
use Typo3Update\Options;

/**
 * Detect missing namespaces for class definitions.
 */
class Typo3Update_Sniffs_LegacyClassname_MissingNamespaceSniff implements PhpCsSniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
        ];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PhpCsFile $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $namespacePosition = $phpcsFile->findPrevious(T_NAMESPACE, $stackPtr);
        if ($namespacePosition !== false) {
            return;
        }
        $classnamePosition = $phpcsFile->findNext(T_STRING, $stackPtr);

        if ($classnamePosition === false) {
            return;
        }

        $classname = $tokens[$classnamePosition]['content'];

        LegacyClassnameMapping::getInstance()->addLegacyClassname(
            $classname,
            $this->getNamespace($classname) . '\\' . $this->getNewClassname($classname)
        );
        $fix = $phpcsFile->addFixableError(
            'Legacy class definitions are not allowed; found "%s".'
                . ' Wrap your class inside a namespace.',
            $classnamePosition,
            'legacyClassname',
            [$classname]
        );
        if ($fix === true) {
            $this->replaceLegacyClassname($phpcsFile, $classnamePosition, $classname);
        }
    }

    /**
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     */
    protected function replaceLegacyClassname(
        PhpCsFile $phpcsFile,
        $classnamePosition,
        $classname
    ) {
        $tokens = $phpcsFile->getTokens();
        $lineEndings = PhpCsFile::detectLineEndings($phpcsFile->getFilename());
        $suffix = $lineEndings;

        if ($tokens[1]['code'] !== T_WHITESPACE) {
            $suffix .= $lineEndings;
        }

        $phpcsFile->fixer->replaceToken(
            $classnamePosition,
            substr($classname, strrpos($classname, '_') + 1)
        );
        $phpcsFile->fixer->replaceToken(
            $this->getNamespacePosition($phpcsFile),
            '<?php' . $lineEndings . $this->getNamespaceDefinition($classname) . $suffix
        );
    }

    /**
     * @param string $classname
     * @return string
     */
    protected function getNewClassname($classname)
    {
        return substr($classname, strrpos($classname, '_') + 1);
    }

    /**
     * @param PhpCsFile $phpcsFile
     * @return int|false
     */
    protected function getNamespacePosition(PhpCsFile $phpcsFile)
    {
        return $phpcsFile->findNext(T_OPEN_TAG, 0);
    }

    /**
     * Returns whole statement to define namespace.
     *
     * E.g. namespace VENDOR\ExtName\FolderName;
     *
     * @param string $classname
     * @return string
     */
    protected function getNamespaceDefinition($classname)
    {
        return 'namespace ' . $this->getNamespace($classname) . ';';
    }

    /**
     * Returns namespace, based on legacy class name.
     *
     * E.g. Tx_ExtName_FolderName_FileName -> VENDOR\ExtName\FolderName
     *
     * @param string $classname
     * @return string
     */
    protected function getNamespace($classname)
    {
        $vendor = trim(Options::getVendor(), '\\/');
        $classnameParts = explode('_', $classname);

        unset($classnameParts[0]); // Remove Tx_
        unset($classnameParts[count($classnameParts)]); // Remove class name itself.

        return $vendor . '\\' . implode('\\', $classnameParts);
    }
}
