<?php

use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use PHP_CodeSniffer_File as PhpCsFile;
use PHP_CodeSniffer_Sniff as PhpCsSniff;

class Typo3Update_Sniffs_TypoScript_ViewConfigurationSniff implements PhpCsSniff
{
    public $supportedTokenizers = [
        'TYPOSCRIPT',
    ];

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            TokenInterface::TYPE_OBJECT_IDENTIFIER,
        ];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * This is the default implementation, as most of the time next T_STRING is
     * the class name. This way only the register method has to be registered
     * in default cases.
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
        $token = $tokens[$stackPtr];

        if ($token['content'] === 'layoutRootPath') {
            $phpcsFile->addWarning(
                'Do not use %s anymore, use %s instead.',
                $stackPtr,
                'legacy',
                [
                    $token['content'],
                    'layoutRootPaths'
                ]
            );
        }
    }
}
