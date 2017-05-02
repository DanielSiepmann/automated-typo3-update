.. _features:

Features
========

Migration of old legacy classnames to namespace class names
-----------------------------------------------------------

Currently we can migrate calls to old legacy class names of the TYPO3 core like ``Tx_Extbase...`` to
new ones like ``\TYPO3\Extbase\...``. This is done for:

Possible configurations for all sniffs:

- :ref:`configuration-legacyExtensions`

Implemented sniffs:

- PHPDocComments, like Includes and annotations for IDEs.

  Possible extra configurations:

  - :ref:`configuration-allowedTags`


- Inheritance like ``extends`` and ``implements``.

- Static calls like ``t3lib_div::`` to ``\TYPO3\Core\Utility\GeneralUtility``.

- Static call also checks for ``::class``, as technically we just look before the ``::``.

- Typehints in methods and function like injects.

- ``instanceof`` checks.

- Inline comments for IDEs, e.g. ``/* @var $configurationManager
  Tx_Extbase_Configuration_ConfigurationManager */``

- Instantiation through ``new``.

- Instantiation through ``makeInstance``. Only Classnames in Strings are supported, no ``::class``.

- Instantiation through ``ObjectManager``, check afterwards as this is static and all function calls
  using ``get`` and ``create`` will be adjusted. Might be useful to exclude this sniff and run it
  separately.
  Only Classnames in Strings are supported, no ``::class``.

- ``use`` statements.

- ``catch`` of legacy class names.


Also definitions of classes, traits and interfaces are migrated too:

Possible extra configurations:

- :ref:`configuration-vendor`


Definitions are migrated, where namespace is added right after opening php-tag and class name is
replaced with last part. We split by ``_`` as Extbase convention.

After definitions were migrated, we also migrate the usage in the same way as documented above for
TYPO3 core classes. On first run the definition will be converted, on second run the usage. This is
due to the fact, that PHPCS might find the definition after the usage, so please run twice.

.. note::
   The configured file will be updated after each run, for each converted class, trait and
   interface definition. See :ref:`configuration-mappingFile`.


This also covers adding the vendor to plugin and modules in :file:`ext_tables.php` and
:file:`ext_localconf.php`:

Possible extra configurations:

- :ref:`configuration-vendor`


Add missing vendor to plugin and module registrations and configurations.  You might want to set
this to non fixable and warning if you already provide the vendor inside a single Variable, together
with your extension key, as this is not recognized. So the following will be recognized:

  - ``$_EXTKEY,``

  - ``$VENDOR . $_EXTKEY,``

  - ``'VENDOR.' . $_EXTKEY,``


While the following will not:

  - ``$key = 'Vendor.' . $_EXTKEY;``

Check for removed calls
-----------------------

Also we check for the following deprecated calls:

Check for usage of *removed functions* in general. The functions are configured via yaml files. The
location of them is configurable, default is inside the standard itself, and we try to deliver all
information. For configuration options see :ref:`configuration-removedFunctionConfigFiles`.

Check for usage of *removed constants*. The constants are configured in same way as removed
functions. For configuration options see :ref:`configuration-removedConstantConfigFiles`.

Check for usage of *removed signals*. The signals are configured in same way as removed
functions. For configuration options see :ref:`configuration-removedSignalConfigFiles`.

Check for usage of *removed TypoScript*. The TypoScript objects are configured in same way as
removed functions. For configuration options see :ref:`configuration-removedTypoScriptConfigFiles`.
This will check whether you are using already removed TypoScript parts, supported are:

- Objects, e.g. ``CLEARGIF``, ``FORM``

- Paths like ``styles.insertContent``

For a complete list, take a look at the corresponding YAML-Files.

Further checks
--------------

- Legacy ajax registrations for TYPO3 Backend.
