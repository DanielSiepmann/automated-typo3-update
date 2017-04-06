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

TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $_EXTKEY,
    'name',
    [
        'Controller' => 'action',
    ]
);

Tx_Extbase_Utility_Extension::configurePlugin(
    $_EXTKEY,
    'name',
    [
        'Controller' => 'action',
    ]
);

Tx_Extbase_Utility_Extension::registerPlugin(
    $_EXTKEY,
    'name',
    'title'
);

TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'name',
    'title'
);

Tx_Extbase_Utility_Extension::registerModule(
    $_EXTKEY,
    'subpart',
    'key'
    '',
    [
        'Controller' => 'action',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    $_EXTKEY,
    'subpart',
    'key'
    '',
    [
        'Controller' => 'action',
    ]
);

// Already vendor exists

TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Vendor.' . $_EXTKEY,
    'name',
    [
        'Controller' => 'action',
    ]
);

Tx_Extbase_Utility_Extension::configurePlugin(
    'Vendor.' . $_EXTKEY,
    'name',
    [
        'Controller' => 'action',
    ]
);

Tx_Extbase_Utility_Extension::registerPlugin(
    'Vendor.' . $_EXTKEY,
    'name',
    'title'
);

TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Vendor.' . $_EXTKEY,
    'name',
    'title'
);

Tx_Extbase_Utility_Extension::registerModule(
    'Vendor.' . $_EXTKEY,
    'subpart',
    'key'
    '',
    [
        'Controller' => 'action',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Vendor.' . $_EXTKEY,
    'subpart',
    'key'
    '',
    [
        'Controller' => 'action',
    ]
);
