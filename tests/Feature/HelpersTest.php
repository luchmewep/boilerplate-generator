<?php

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

uses()->group('helpers');

/***** PATHS *****/

it('can create package-domain base path', function (?string $package, ?string $domain, ?string $expected) {
    expect(package_domain_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => null,
    ],
])->group('base', 'path');

it('can create package-domain app path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_app_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_app_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/src',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/src',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/src',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'app',
    ],
])->group('app', 'path');

it('can create package-domain database path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_database_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_database_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/database',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/database',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/database',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'database',
    ],
])->group('database', 'path');

it('can create package-domain migrations path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_migrations_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_migrations_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/database/migrations',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/database/migrations',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/database/migrations',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'database/migrations',
    ],
])->group('migrations', 'path');

it('can create package-domain seeders path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_seeders_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_seeders_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/database/seeders',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/database/seeders',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/database/seeders',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'database/seeders',
    ],
])->group('seeders', 'path');

it('can create package-domain factories path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_factories_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_factories_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/database/factories',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/database/factories',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/database/factories',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'database/factories',
    ],
])->group('factories', 'path');

it('can create package-domain resources path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_resources_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_resources_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/resources',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/resources',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/resources',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'resources',
    ],
])->group('resources', 'path');

it('can create package-domain views path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_views_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_views_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/resources/views',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/resources/views',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/resources/views',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'resources/views',
    ],
])->group('views', 'path');

it('can create package-domain lang path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_lang_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_lang_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/resources/lang',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/resources/lang',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/resources/lang',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'resources/lang',
    ],
])->group('lang', 'path');

it('can create package-domain tests path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_tests_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_tests_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/tests',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/tests',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/tests',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'tests',
    ],
])->group('tests', 'path');

it('can create package-domain routes path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_routes_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_routes_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/routes',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/routes',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/routes',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'routes',
    ],
])->group('routes', 'path');

it('can create package-domain config path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_config_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_config_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/config',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/config',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/config',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'config',
    ],
])->group('config', 'path')->only();

it('can create package-domain helpers path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_helpers_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_helpers_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/helpers',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/helpers',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/helpers',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'helpers',
    ],
])->group('helpers', 'path');

it('can create package-domain domains path', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_domains_path($package, $domain, true))
        ->toBe(base_path($expected))
        ->and(package_domain_domains_path($package, $domain, true, false))
        ->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'packages/luchavez/test-package/domains/Hello/domains/World/domains',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'packages/luchavez/test-package/domains',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'domains/Hello/domains/World/domains',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'domains',
    ],
])->group('domains', 'path');

/***** NAMESPACES *****/

it('can create package-domain base namespace', function (?string $package, ?string $domain, ?string $expected) {
    expect(package_domain_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => null,
    ],
])->group('base', 'namespace');

it('can create package-domain app namespace', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_app_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World\\',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage\\',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World\\',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'App\\',
    ],
])->group('app', 'namespace');

it('can create package-domain database namespace', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_database_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World\\Database',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage\\Database',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World\\Database',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'Database',
    ],
])->group('database', 'namespace');

it('can create package-domain seeders namespace', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_seeders_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World\\Database\\Seeders\\',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage\\Database\\Seeders\\',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World\\Database\\Seeders\\',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'Database\\Seeders\\',
    ],
])->group('namespace', 'seeders');

it('can create package-domain factories namespace', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_factories_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World\\Database\\Factories\\',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage\\Database\\Factories\\',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World\\Database\\Factories\\',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'Database\\Factories\\',
    ],
])->group('namespace', 'factories');

it('can create package-domain tests namespace', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_tests_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World\\Tests\\',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage\\Tests\\',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World\\Tests\\',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'Tests\\',
    ],
])->group('namespace', 'tests');

it('can create package-domain domains namespace', function (?string $package, ?string $domain, string $expected) {
    expect(package_domain_domains_namespace($package, $domain, true))->toBe($expected);
})->with([
    'both package and domain' => [
        'package' => 'luchavez/test-package',
        'domain' => 'Hello.World',
        'expected' => 'Luchavez\\TestPackage\\Domains\\Hello\\Domains\\World\\Domains\\',
    ],
    'package only' => [
        'package' => 'luchavez/test-package',
        'domain' => null,
        'expected' => 'Luchavez\\TestPackage\\Domains\\',
    ],
    'domain only' => [
        'package' => null,
        'domain' => 'Hello.World',
        'expected' => 'Domains\\Hello\\Domains\\World\\Domains\\',
    ],
    'no package and no domain' => [
        'package' => null,
        'domain' => null,
        'expected' => 'Domains\\',
    ],
])->group('namespace', 'domains');

/***** COMPOSER JSON *****/

it('can get contents of composer.json at base path', function () {
    $contents = getContentsFromComposerJson();
    expect($contents)
        ->toBeIterable()
        ->toMatchArray([
            'name' => 'laravel/laravel',
            'type' => 'project',
            'description' => 'The Laravel Framework.',
        ]);
})->group('composer');

it('can set contents of composer.json at base path')
    ->skip('Skipped because it can cause Composer issues')->group('composer');

it('can add and remove contents in composer.json', function (string $key, mixed $value) {
    assertTrue(add_contents_to_composer_json(dot_notation_key: $key, contents: $value));
    assertFalse(add_contents_to_composer_json(dot_notation_key: $key, contents: $value));

    if (is_array($value)) {
        assertTrue(remove_contents_from_composer_json(dot_notation_key: $key, contents: $value));
        assertFalse(remove_contents_from_composer_json(dot_notation_key: $key, contents: $value));
    }

    assertTrue(remove_contents_from_composer_json(dot_notation_key: $key));
    assertFalse(remove_contents_from_composer_json(dot_notation_key: $key));
})->with([
    'string value' => [
        'key' => 'extra.test.string',
        'value' => 'Hello',
    ],
    'array value' => [
        'key' => 'extra.test.array',
        'value' => [
            'Hello World',
        ],
    ],
    'associative array value' => [
        'key' => 'extra.test.associative',
        'value' => [
            'hello' => 'world',
        ],
    ],
])->group('composer');
