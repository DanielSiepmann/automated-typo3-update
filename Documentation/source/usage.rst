.. _highlight: bash

Usage
=====

If everything is configured, you can run::

    ./vendor/bin/phpcbf <path>

This will run the auto fixer recursive for ``<path>`` fixing all issues.

For some tasks you need to run the above command twice, e.g. for namespace migrations.

Afterwards you should run::

    ./vendor/bin/phpcs <path>

To get information about possible issues that were not autofixed.

To prevent issues, use the following setup::

    ./vendor/bin/phpcs --standard=Typo3Update -p --colors --runtime-set mappingFile <pathToMappingFile> <pathToCodeToCheck>

Same for ``phpcbf``.

Further examples
----------------

You might want to add ``-p --colors`` to see that something is happening.

Also make sure to ignore certain files like libraries or js and css files while running the update.
Check out the official docs for how to do so.

FAQ
---

I do not see any issues regarding TYPO3 update but lots of coding style.
    Then you probably have a :file:`phpcs.xml` in your project taking precedence. Add the
    ``-standard=`` argument to the call::

        ./vendor/bin/phpcs --standard=Typo3Update <path>

I see the error message ``Failed opening required 'Standards/Typo3Update/Sniffs/../../../../LegacyClassnames.php'``
    Then you didn't configure :ref:`configuration-mappingFile`, check the link and update the
    configuration.
