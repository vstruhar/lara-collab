<?php

declare(strict_types=1);

/**
 * PHP 8.5 deprecates ReflectionMethod::setAccessible() and
 * ReflectionProperty::setAccessible(). Those calls have had no effect since
 * PHP 8.1, but Pest 2 still makes them and PHPUnit reports the deprecation.
 *
 * Drop the calls from the installed Pest sources. Safe to run more than once.
 */
$files = [
    dirname(__DIR__).'/vendor/pestphp/pest/src/Support/Reflection.php',
    dirname(__DIR__).'/vendor/pestphp/pest/src/Subscribers/EnsureIgnorableTestCasesAreIgnored.php',
    dirname(__DIR__).'/vendor/pestphp/pest-plugin-arch/src/Support/AssertLocker.php',
];

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }

    $contents = file_get_contents($file);

    if ($contents === false) {
        fwrite(STDERR, "Could not read {$file}".PHP_EOL);
        exit(1);
    }

    $updated = preg_replace(
        '/^[ \t]*\$(?:reflectionMethod|property)->setAccessible\(true\);\R/m',
        '',
        $contents,
    );

    if (! is_string($updated)) {
        fwrite(STDERR, "Could not update {$file}".PHP_EOL);
        exit(1);
    }

    if ($updated === $contents) {
        continue;
    }

    if (file_put_contents($file, $updated) === false) {
        fwrite(STDERR, "Could not write {$file}".PHP_EOL);
        exit(1);
    }
}
