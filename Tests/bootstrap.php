<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Dotenv\Dotenv;

$file = __DIR__ . '/../vendor/autoload.php';
if (!\file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

require $file;
require_once __DIR__ . '/prophecy_php_7_2.php';

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (\is_array($env = @include \dirname(__DIR__) . '/.env.local.php')) {
    $_SERVER += $env;
    $_ENV += $env;
} elseif (!\class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} else {
    $path = \dirname(__DIR__) . '/Tests/Application/.env';
    $dotenv = new Dotenv();

    // load all the .env files
    if (\method_exists($dotenv, 'loadEnv')) {
        $dotenv->loadEnv($path);
    } else {
        // fallback code in case your Dotenv component is not 4.2 or higher (when loadEnv() was added)

        if (\file_exists($path) || !\file_exists($p = "$path.dist")) {
            $dotenv->load($path);
        } else {
            $dotenv->load($p);
        }

        if (null === $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) {
            $dotenv->populate(['APP_ENV' => $env = 'dev']);
        }

        if ('test' !== $env && \file_exists($p = "$path.local")) {
            $dotenv->load($p);
            $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? $env;
        }

        if (\file_exists($p = "$path.$env")) {
            $dotenv->load($p);
        }

        if (\file_exists($p = "$path.$env.local")) {
            $dotenv->load($p);
        }
    }
}

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || \filter_var($_SERVER['APP_DEBUG'], \FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
