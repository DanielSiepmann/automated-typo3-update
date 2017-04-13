.. _configuration:

Configuration
=============

Configuration is done through PHPCS Standards, e.g. provide a custom :file:`ruleset.xml` or inside your
project using a :file:`phpcs.xml.dist`. As this is just a PHPCS-Standard, the official documentation
applies.

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
----------------

Configures which extension names are legacy. Used to provide further checks and warnings about
possible legacy code. All class usages starting with ``Tx_<ExtensionName>`` where ExtensionName is
defined in this array, will produce a warning, until the class is already found to be deprecaed.

Can and have to be configured for each sniff, e.g. ``Instanceof`` and ``DocComment``.

Example:

.. code:: xml

  <rule ref="Typo3Update.LegacyClassnames.Instanceof">
      <properties>
          <property name="legacyExtensions" type="array" value="Extbase,Fluid,Frontend,Core"/>
      </properties>
  </rule>


.. _configuration-allowedTags:

allowedTags
-----------

Only used inside Sniff ``Typo3Update.LegacyClassnames.DocComment``.

Configures which tags are checked for legacy class names.

This way you can add checks for further tags you are using. All strings inside the tag are checked,
so no matter where the class name occurs inside the tag.

Example:

.. code:: xml

   <rule ref="Typo3Update.LegacyClassnames.DocComment">
       <properties>
           <property name="allowedTags" type="array" value="@param,@return,@var,@see,@throws"/>
       </properties>
   </rule>

.. _configuration-mappingFile:

mappingFile
-----------

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
------

Used while adding namespaces to legacy class definitions and updating plugin and module
registrations. Default is ``YourCompany`` to enable you to search and replace afterwards.

If you use multiple vendors through your projects, use the cli to define the vendor and run
``phpcbf`` over specific folders, this way you can update your project step by step with different
vendors.

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="vendor" value="YourVendor"/>

Example:

.. code:: bash

    --runtime-set vendor YourVendor

.. _configuration-removedFunctionConfigFiles:

removedFunctionConfigFiles
--------------------------

Configure where to look for configuration files defining the removed functions and methods. Default
is ``Configuration/Removed/Functions/*.yaml`` inside the standard itself. We already try to deliver
as much as possible.
Globing is used, so placeholders like ``*`` are possible, see
https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedFunctionConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Example:

.. code:: bash

    --runtime-set removedFunctionConfigFiles "/Some/Absolute/Path/*.yaml"

.. _configuration-removedConstantConfigFiles:

removedConstantConfigFiles
--------------------------

Configure where to look for configuration files defining the removed constants. Default is
``Configuration/Removed/Functions/*.yaml`` inside the standard itself. We already try to deliver as
much as possible.  Globing is used, so placeholders like ``*`` are possible, see
https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedConstantConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Example:

.. code:: bash

    --runtime-set removedConstantConfigFiles "/Some/Absolute/Path/*.yaml"

.. _configuration-removedTypoScriptObjectIdentifierConfigFiles:

removedTypoScriptObjectIdentifierConfigFiles
--------------------------------------------

Configure where to look for configuration files defining the removed TypoScript object identifiers.
Default is ``Configuration/Removed/TypoScript/ObjectIdentifier/*.yaml`` inside the standard itself.
We already try to deliver as much as possible.  Globing is used, so placeholders like ``*`` are
possible, see https://secure.php.net/manual/en/function.glob.php

Using :file:`ruleset.xml`:

.. code:: xml

    <config name="removedTypoScriptObjectIdentifierConfigFiles" value="/Some/Absolute/Path/*.yaml"/>

Example:

.. code:: bash

    --runtime-set removedTypoScriptObjectIdentifierConfigFiles "/Some/Absolute/Path/*.yaml"
