<?php

/**
 * Created by PhpStorm.
 * User: dah
 * Date: 11.04.17
 * Time: 12:12
 */
class Typo3Update_Sniffs_Removed_ExtensionKeySniff extends \Typo3Update\Sniffs\Removed\AbstractGenericUsage
{
    /**
     * Return file names containing removed configurations.
     *
     * @return array<string>
     */
    protected function getRemovedConfigFiles()
    {
        return [];
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
        return '';
    }

    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * An example return value for a sniff that wants to listen for whitespace
     * and any comments would be:
     *
     * <code>
     *    return array(
     *            T_WHITESPACE,
     *            T_DOC_COMMENT,
     *            T_COMMENT,
     *           );
     * </code>
     *
     * @return int[]
     * @see    Tokens.php
     */
    public function register()
    {
        return [];
    }
}
