<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MetadatasTagsFixture
 *
 * Links:
 *   scan     -> foo 1.0.0 and foo 2.0.0  (=> related package: foo)
 *   protocol -> bar 1.0                  (=> related package: bar)
 */
class MetadatasTagsFixture extends TestFixture
{
    public array $records = [
        [
            'metadata_id' => 'aaaaaaaa-0001-4000-8000-000000000001',
            'tag_id' => 'dddddddd-0001-4000-8000-000000000001',
        ],
        [
            'metadata_id' => 'aaaaaaaa-0002-4000-8000-000000000002',
            'tag_id' => 'dddddddd-0001-4000-8000-000000000001',
        ],
        [
            'metadata_id' => 'bbbbbbbb-0001-4000-8000-000000000001',
            'tag_id' => 'dddddddd-0002-4000-8000-000000000002',
        ],
    ];
}
