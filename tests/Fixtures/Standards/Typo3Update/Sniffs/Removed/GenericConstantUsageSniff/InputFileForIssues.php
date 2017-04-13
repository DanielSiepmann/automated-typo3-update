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

$file = PATH_tslib . 'something.php';
$module = TYPO3_MOD_PATH . '/folder';
$someVariable = \TYPO3\CMS\IndexedSearch\Controller\SearchFormController::WILDCARD_LEFT . '/folder';
use \TYPO3\CMS\IndexedSearch\Controller\SearchFormController;
// Will work, as we only check last part of class name.
$someVariable = SearchFormController::WILDCARD_LEFT . '/folder';
// Also matches, as we don't check whole name for now, to enable the above check with imported name.
$someVariable = \Vendor\ExtName\Controller\SearchFormController::WILDCARD_LEFT . '/folder';
