<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MetadatasFixture
 *
 * foo has two versions (1.0.0 and 2.0.0) to exercise the version selector on
 * the package view. bar carries a package_ci JSON blob to exercise the
 * "Package Checks" rendering. Descriptions use distinct tokens (Alpha/Beta/
 * Gamma) so search assertions can target a single package.
 */
class MetadatasFixture extends TestFixture
{
    public array $records = [
        // foo 1.0.0
        [
            'id' => 'aaaaaaaa-0001-4000-8000-000000000001',
            'package_id' => '11111111-1111-4111-8111-111111111111',
            'version' => '1.0.0',
            'description' => 'Alpha package for scanning traffic.',
            'script_dir' => 'scripts',
            'plugin_dir' => null,
            'build_command' => null,
            'user_vars' => null,
            'test_command' => null,
            'config_files' => null,
            'depends' => null,
            'external_depends' => null,
            'suggests' => null,
            'package_ci' => null,
            'created' => '2024-01-01 00:00:00',
            'modified' => '2024-01-01 00:00:00',
        ],
        // foo 2.0.0 (latest; shown first on the index)
        [
            'id' => 'aaaaaaaa-0002-4000-8000-000000000002',
            'package_id' => '11111111-1111-4111-8111-111111111111',
            'version' => '2.0.0',
            'description' => 'Alpha package version two.',
            'script_dir' => 'scripts',
            'plugin_dir' => null,
            'build_command' => null,
            'user_vars' => null,
            'test_command' => null,
            'config_files' => null,
            'depends' => null,
            'external_depends' => null,
            'suggests' => null,
            'package_ci' => null,
            'created' => '2024-06-15 12:00:00',
            'modified' => '2024-06-15 12:00:00',
        ],
        // bar 1.0 (with CI results)
        [
            'id' => 'bbbbbbbb-0001-4000-8000-000000000001',
            'package_id' => '22222222-2222-4222-8222-222222222222',
            'version' => '1.0',
            'description' => 'Beta protocol analyzer.',
            'script_dir' => null,
            'plugin_dir' => 'build',
            'build_command' => null,
            'user_vars' => null,
            'test_command' => null,
            'config_files' => null,
            'depends' => null,
            'external_depends' => null,
            'suggests' => null,
            'package_ci' => '{"ok":true,"checks":[{"name":"build_zeek","ok":true},{"name":"dns_resolution","ok":false,"errors":["lookup failed"]}]}',
            'created' => '2024-02-01 08:30:00',
            'modified' => '2024-02-01 08:30:00',
        ],
        // baz 0.1
        [
            'id' => 'cccccccc-0001-4000-8000-000000000001',
            'package_id' => '33333333-3333-4333-8333-333333333333',
            'version' => '0.1',
            'description' => 'Gamma utility helper.',
            'script_dir' => null,
            'plugin_dir' => null,
            'build_command' => null,
            'user_vars' => null,
            'test_command' => null,
            'config_files' => null,
            'depends' => null,
            'external_depends' => null,
            'suggests' => null,
            'package_ci' => null,
            'created' => '2024-01-03 00:00:00',
            'modified' => '2024-01-03 00:00:00',
        ],
    ];
}
