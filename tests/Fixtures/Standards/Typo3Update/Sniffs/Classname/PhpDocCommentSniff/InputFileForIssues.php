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

class InputFileForIssues
{
    /**
     * @var Tx_Extbase_Domain_Repository_CategoryRepository
     * @inject
     */
    protected $someVar;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Tx_Extbase_Domain_Model_Category>
     * @validate Tx_Extbase_Validation_Validator_NumberValidator(property="value")
     */
    protected $someRelation;

    /**
     * @param t3lib_div
     * @param \TYPO3\CMS\Backend\Template\MediumDocumentTemplate
     * @param \TYPO3\CMS\Perm\Controller\PermissionAjaxController
     *
     * @return Tx_Extbase_Configuration_Configurationmanager
     */
    public function doSomething($something)
    {
        /** @var t3lib_div $variable */
        /** @var $variable t3lib_div */
    }
}
