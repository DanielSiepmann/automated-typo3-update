.. _highlight: bash

About
=====

Our goal is to provide automated migrations for TYPO3 updates, as much as possible.

This should include source code modifications like adjusting old legacy class names to new ones and
providing a list of deprecated calls.

The official project home page can be found at https://git.higidi.com/Automated-TYPO3-Update/automated-typo3-update .
Please open new issues and merge requests there. You can login with your Github account.

Github is just used as a mirror for the project.

Requirements
============

To install the project you need ``composer`` to be installed and inside your ``$PATH``.
Otherwise run ``make install-composer`` to install composer.

We recommend to use at least PHP 5.6, we do not test with lower versions as 5.6 is latest maintained
version.

Installation
============

Run::

    make install

Afterwards the :ref:`configuration-mappingFile` is required.

What does it look like?
=======================

.. code::

   $ ./vendor/bin/phpcs -p --colors -s <path>
   E


   FILE: <path>
   ----------------------------------------------------------------------
   FOUND 5 ERRORS AFFECTING 5 LINES
   ----------------------------------------------------------------------
    8 | ERROR | [x] Legacy classes are not allowed; found
      |       |   backend_toolbarItem
      |       |   (Typo3Update.LegacyClassnames.Inheritance.legacyClassname)
   14 | ERROR | [x] Legacy classes are not allowed; found TYPO3backend
      |       |   (Typo3Update.LegacyClassnames.DocComment.legacyClassname)
   16 | ERROR | [x] Legacy classes are not allowed; found TYPO3backend
      |       |   (Typo3Update.LegacyClassnames.TypeHint.legacyClassname)
   48 | ERROR | [x] Legacy classes are not allowed; found t3lib_extMgm
      |       |   (Typo3Update.LegacyClassnames.StaticCall.legacyClassname)
   61 | ERROR | [x] Legacy classes are not allowed; found t3lib_div
      |       |   (Typo3Update.LegacyClassnames.StaticCall.legacyClassname)
   ----------------------------------------------------------------------
   PHPCBF CAN FIX THE 5 MARKED SNIFF VIOLATIONS AUTOMATICALLY
   ----------------------------------------------------------------------

   Time: 35ms; Memory: 5Mb

.. toctree::
   :maxdepth: 2
   :hidden:

   features
   configuration
   usage
   extending
   contribution
