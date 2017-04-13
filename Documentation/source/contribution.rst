.. _highlight: bash

Contribution
============

The project is hosted at https://git.higidi.com/Automated-TYPO3-Update/automated-typo3-update fill
issues there. Also you can fork and clone the project there and provide merge requests.

Also you can contact us on `TYPO3 slack`_.

Documentation
-------------

Documentation is written using `reStructuredText`_ ans `sphinx`_.

Just open the files with a text editor and update contents.

To render documentation locally install `docker`_ and run::

    docker run -v "$PWD/Documentation":/sphinx danielsiepmann/sphinx

from within the project root.

Code
----

A :file:`.editorconfig` is already provided to setup your editor. Also `phpcs` is configured, so
make sure to check your coding style with `phpcs`_.

New sniffs have to be covered by tests, see :ref:`extending-tests`.

.. _TYPO3 slack: https://typo3.slack.com/messages/@danielsiepmann
.. _docker: https://www.docker.com/
.. _phpcs: https://github.com/squizlabs/PHP_CodeSniffer
.. _reStructuredText: http://docutils.sourceforge.net/rst.html
.. _sphinx: http://www.sphinx-doc.org/en/stable/
