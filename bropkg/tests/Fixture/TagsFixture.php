<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TagsFixture
 *
 * "scan" is attached to foo's metadata, "protocol" to bar's. "unused" has no
 * associations so tests can cover a tag with no related packages.
 */
class TagsFixture extends TestFixture
{
    public array $records = [
        [
            'id' => 'dddddddd-0001-4000-8000-000000000001',
            'name' => 'scan',
            'created' => '2024-01-01 00:00:00',
            'modified' => '2024-01-01 00:00:00',
        ],
        [
            'id' => 'dddddddd-0002-4000-8000-000000000002',
            'name' => 'protocol',
            'created' => '2024-01-01 00:00:00',
            'modified' => '2024-01-01 00:00:00',
        ],
        [
            'id' => 'dddddddd-0003-4000-8000-000000000003',
            'name' => 'unused',
            'created' => '2024-01-01 00:00:00',
            'modified' => '2024-01-01 00:00:00',
        ],
    ];
}
