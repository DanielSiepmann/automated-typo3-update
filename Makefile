BIN_PHPCS = ./vendor/bin/phpcs
BIN_PHPCBF = ./vendor/bin/phpcbf
DEFAULT_STANDARD = Typo3Update
CUSTOM_STANDARDS = $(abspath ./src/Standards/)

install:
	composer install
	$(BIN_PHPCS) --config-set installed_paths $(CUSTOM_STANDARDS)
	$(BIN_PHPCS) -i | grep Typo3Update
	$(BIN_PHPCS) --config-set default_standard $(DEFAULT_STANDARD)

# For development / testing purposes:
test-search:
	$(BIN_PHPCS) -p --colors -s PROJECT_PATH
test-fix:
	$(BIN_PHPCBF) -p --colors -s PROJECT_PATH
