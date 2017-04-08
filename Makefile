BIN_PHPCS = ./vendor/bin/phpcs
BIN_PHPCBF = ./vendor/bin/phpcbf
DEFAULT_STANDARD = Typo3Update
CUSTOM_STANDARDS = $(abspath ./src/Standards/)

install:
	composer install --optimize-autoloader --no-interaction --no-ansi
	$(BIN_PHPCS) --config-set installed_paths $(CUSTOM_STANDARDS)
	$(BIN_PHPCS) -i | grep Typo3Update
	$(BIN_PHPCS) --config-set default_standard $(DEFAULT_STANDARD)

# For development / testing purposes:
test-search:
	$(BIN_PHPCS) -p --colors -s PROJECT_PATH
test-fix:
	$(BIN_PHPCBF) -p --colors -s PROJECT_PATH

# For CI:
install-composer:
	wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	php composer-setup.php
	php -r "unlink('composer-setup.php'); unlink('installer.sig');"
	chmod ugo+x composer.phar
	mv composer.phar /usr/local/bin/composer
