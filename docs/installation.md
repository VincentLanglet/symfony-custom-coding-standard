# Installation

This standard can be installed with the [Composer](https://getcomposer.org/) dependency manager.

1. Add the coding standard as a dependency of your project

```
composer require --dev vincentlanglet/symfony-custom-coding-standard
```

2. Add the coding standard to the PHP_CodeSniffer install path

The path is relative to the php_codesniffer install path.
This is important to make it work both in your vagrant, local machine and PHPStorm

```
bin/phpcs --config-set installed_paths ../../vincentlanglet/symfony-custom-coding-standard
```

3. Check the installed coding standards

```
bin/phpcs -i
```

4. Done!

```
bin/phpcs --standard=SymfonyCustom /path/to/code
```

5. (optional) Set up PHPStorm

- configure code sniffer under Languages & Frameworks -> PHP -> Code Sniffer
- Go to Editor -> Inspections -> PHP Code sniffer, refresh the standards and select SymfonyCustom
