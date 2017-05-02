.. _highlight: yaml
.. _configuration:

Configuration
=============

Configuration is done through PHPCS Standards, e.g. provide a custom :file:`ruleset.xml` or inside your
project using a :file:`phpcs.xml.dist`. As this is just a PHPCS-Standard, the official documentation
applies.

Also some configuration is done through yaml files, see :ref:`configuration-yaml-files`.

.. _configuration-options:

Options
-------

All options available in :file:`ruleset.xml` are also available in your :file:`phpcs.xml` files, as
already documented by phpcs itself. Therefore this documentation will just mention
:file:`ruleset.xml`.

Beside that, some options are also available through CLI. Examples are always provided.

To disable warnings for specific deprecated parts, e.g. a specific function, you can use the full
sniff name, as we try to add the concrete constant or function name to the sniff. Just run ``phpcs``
with the ``-s`` option to see sniff names.

The following configuration options are available:

.. _configuration-legacyExtensions:

legacyExtensions
^^^^^^^^^^^^^^^^

Configures which extension names are legacy. Used to provide further checks and warnings about
possible legacy code. All class usages starting with ``Tx_<ExtensionName>`` where ExtensionName is
defined in this array, will produce a warning, until the class is already found to be deprecaed.

Can and have to be configured for each sniff, e.g. ``Instanceof`` and ``PhpDocComment``.

Example:

.. code:: xml

  <rule ref="Typo3Update.Classname.Instanceof">
      <properties>
          <property name="legacyExtensions" type="array" value="Extbase,Fluid,Frontend,Core"/>
      </properties>
  </rule>


.. _configuration-allowedTags:

allowedTags
^^^^^^^^^^^

Only used inside Sniff ``Typo3Update.Classname.PhpDocComment``.

Configures which tags are checked for legacy class names.

This way you can add checks for further tags you are using. All strings inside the tag are checked,
so no matter where the class name occurs inside the tag.

Example:

.. code:: xml

   <rule ref="Typo3Update.Classname.PhpDocComment">
       <properties>
           <property name="allowedTags" type="array" value="@param,@return,@var,@see,@throws"/>
       </properties>
   </rule>

.. _configuration-mappingFile:

mappingFile
^^^^^^^^^^^

For auto migrating usages of old class names, a PHP file with a mapping is required. The file has to
be in the composer structure :file:`autoload_classaliasmap.php`.
If TYPO3 is already installed using composer, you can use this file through configuration, or by
copying to the default location, which is :file:`LegacyClassnames.php` in the root of this project.

Configure where the `LegacyClassnames.php` is located, through ``ruleset.xml`` or using
``--runtime-set``. Default is `LegacyClassnames.php` in the project root.

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="mappingFile" value="/projects/typo3_installation/vendor/composer/autoload_classaliasmap.php"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set mappingFile /projects/typo3_installation/vendor/composer/autoload_classaliasmap.php

.. _configuration-vendor:

vendor
^^^^^^

Used while adding namespaces to legacy class definitions and updating plugin and module
registrations. Default is ``YourCompany`` to enable you to search and replace afterwards.

If you use multiple vendors through your projects, use the cli to define the vendor and run
``phpcbf`` over specific folders, this way you can update your project step by step with different
vendors.

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="vendor" value="YourVendor"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set vendor YourVendor

.. _configuration-removedFunctionConfigFiles:

removedFunctionConfigFiles
^^^^^^^^^^^^^^^^^^^^^^^^^^

Configure where to look for configuration files defining the removed functions and methods. Default
is ``Configuration/Removed/Functions/*.yaml`` inside the standard itself. We already try to deliver
as much as possible.
Globing is used, so placeholders like ``*`` are possible, see
https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedFunctionConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set removedFunctionConfigFiles "/Some/Absolute/Path/*.yaml"

.. _configuration-removedSignalConfigFiles:

removedSignalConfigFiles
^^^^^^^^^^^^^^^^^^^^^^^^^^

Configure where to look for configuration files defining the removed signals. Default
is ``Configuration/Removed/Signals/*.yaml`` inside the standard itself. We already try to deliver
as much as possible.
Globing is used, so placeholders like ``*`` are possible, see
https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedSignalConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set removedSignalConfigFiles "/Some/Absolute/Path/*.yaml"

.. _configuration-removedConstantConfigFiles:

removedConstantConfigFiles
^^^^^^^^^^^^^^^^^^^^^^^^^^

Configure where to look for configuration files defining the removed constants. Default is
``Configuration/Removed/Functions/*.yaml`` inside the standard itself. We already try to deliver as
much as possible.  Globing is used, so placeholders like ``*`` are possible, see
https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedConstantConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set removedConstantConfigFiles "/Some/Absolute/Path/*.yaml"

.. _configuration-removedTypoScriptConfigFiles:

removedTypoScriptConfigFiles
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Configure where to look for configuration files defining the removed TypoScript object identifiers.
Default is ``Configuration/Removed/TypoScript/*.yaml`` inside the standard itself.
We already try to deliver as much as possible.  Globing is used, so placeholders like ``*`` are
possible, see https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedTypoScriptConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set removedTypoScriptConfigFiles "/Some/Absolute/Path/*.yaml"

.. _configuration-features:

features
^^^^^^^^

Configure where to look for configuration files defining the feature mappings. Default is
``Configuration/Features/*.yaml`` inside the standard itself. Globing is used, so placeholders like
``*`` are possible, see https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="features" value="/Some/Absolute/Path/*.yaml"/>

Using ``runtime-set``:

.. code:: bash

    --runtime-set features "/Some/Absolute/Path/*.yaml"

.. _configuration-yaml-files:

YAML Files
----------

YAML files are used to configure removed constants, function / methods and TypoScript. We decided to
go with yaml files here, to ease adding stuff in the future. It's a simple format and everyone can
contribute.

You can configure the paths to look up the files through the specific options, documented above.

This section will cover the structure of the various yaml files.

General structure
^^^^^^^^^^^^^^^^^

The basic structure is the same for all parts. Inside a file you have to provide an array for each
TYPO3 version::

    '7.0':
        styles.insertContent:
            replacement: 'Either remove usage of styles.insertContent or a...'
            docsUrl: 'https://docs.typo3.org/typo3cms/extensions/core/7.6/Changelog/7.0...'
    '7.1':
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController->includeTCA:
            replacement: 'Full TCA is always loaded during bootstrap in ...'
            docsUrl: 'https://docs.typo3.org/typo3cms/extensions/core/7.6/Changelog/7...'

In above example the TypoScript ``styles.insertContent`` was removed in TYPO3 version *7.0*.
Below a TYPO3 version each entry is a removed function or TypoScript part of TYPO3. The key is used
to lookup matchings in the source code. Specific parsing is documented below.

All entries consists of a ``replacement`` and ``docsUrl`` entry.

The ``replacement`` can either be ``null`` or a string. If it's null we will show that this part is
removed without any replacement.
If you provide a string, this will be displayed to help during migrations.

The ``docsUrl`` is displayed in addition, so everyone can take a deeper look at the change, the
effects and how to migrate.

Also the TYPO3 core team references the forge issues in each change, where you can find the pull
requests.

Constants and Functions
^^^^^^^^^^^^^^^^^^^^^^^

Special parsing is done for the keys identifying removed constants and functions.

Always provide the fully qualified class namespace. Seperate the constant or method by ``::`` if
it's possible to access it static, otherwise use ``->`` to indicate it's an instance method.

This is used to check only matching calls.

Two examples::

    '7.0':
        \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA:
            replacement: null
            docsUrl: 'https://docs.typo3.org/typo3cms/extensions/core/7.6/Ch...'
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController->includeTCA:
            replacement: 'Full TCA is always loaded during bootstrap in FE, th...'
            docsUrl: 'https://docs.typo3.org/typo3cms/extensions/core/7.6/Change...'

TypoScript
^^^^^^^^^^

Use ``new`` in front of, to declare the entry as OBJECT, e.g. a cObject.
Only matching types will be checked in source code.

Two examples::

    '7.0':
        styles.insertContent:
            replacement: 'Either remove usage of styles.insertContent or add a sni...'
            docsUrl: 'https://docs.typo3.org/typo3cms/extensions/core/7.6/Changelog...'
    '7.1':
        new HRULER:
            replacement: 'Any installation should migrate to alternatives such as F...'
            docsUrl: 'https://docs.typo3.org/typo3cms/extensions/core/7.6/Changelog...'

Features
^^^^^^^^

Configures which Features should be attached to x Sniffs, where Key is the FQCN of the feature and
the values are FQCN of the sniffs.

Works only if the sniff respects execution of features.

One example::

    Typo3Update\Feature\LegacyClassnameFeature:
      - Typo3Update_Sniffs_Classname_InheritanceSniff
      - Typo3Update_Sniffs_Classname_InlineCommentSniff
      - Typo3Update_Sniffs_Classname_InstanceofSniff
      - Typo3Update_Sniffs_Classname_InstantiationWithMakeInstanceSniff
      - Typo3Update_Sniffs_Classname_InstantiationWithNewSniff
      - Typo3Update_Sniffs_Classname_InstantiationWithObjectManagerSniff
      - Typo3Update_Sniffs_Classname_IsACallSniff
      - Typo3Update_Sniffs_Classname_MissingVendorForPluginsAndModulesSniff
      - Typo3Update_Sniffs_Classname_PhpDocCommentSniff
      - Typo3Update_Sniffs_Classname_StaticCallSniff
      - Typo3Update_Sniffs_Classname_TypeHintCatchExceptionSniff
      - Typo3Update_Sniffs_Classname_TypeHintSniff
      - Typo3Update_Sniffs_Classname_UseSniff
      - Typo3Update_Sniffs_LegacyClassname_MissingNamespaceSniff
