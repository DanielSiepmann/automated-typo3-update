<?php

use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use PHP_CodeSniffer_File as PhpCsFile;
use Typo3Update\Sniffs\Options;
use Typo3Update\Sniffs\Removed\AbstractGenericUsage;

class Typo3Update_Sniffs_Removed_TypoScriptSniff extends AbstractGenericUsage
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
            TokenInterface::TYPE_OBJECT_CONSTRUCTOR,
            TokenInterface::TYPE_OBJECT_IDENTIFIER,
        ];
    }

    /**
     * Prepares structure from config for later usage.
     *
     * @param array $typo3Versions
     * @return array
     */
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
                $config['version_removed'] = $typo3Version;

                $newStructure[$removed] = $config;
            }
        }

        return $newStructure;
    }

    /**
     * Check whether the current token is removed.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    protected function isRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $objectIdentifier = $token['content'];

        if (isset($this->configured[$objectIdentifier]) && $token['type'] === $this->configured[$objectIdentifier]['type']) {
            $this->removed = [
                $this->configured[$objectIdentifier]
            ];
            return true;
        }

        return false;
    }

    /**
     * Identifier for configuring this specific error / warning through PHPCS.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getIdentifier(array $config)
    {
        return str_replace('.', '-', $config['name']);
    }

    /**
     * The original call, to allow user to check matches.
     *
     * As we match the name, that can be provided by multiple classes, you
     * should provide an example, so users can check that this is the legacy
     * one.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getOldUsage(array $config)
    {
        return $config['name'];
    }

    /**
     * Return file names containing removed configurations.
     *
     * @return array<string>
     */
    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedTypoScriptConfigFiles();
    }
}
