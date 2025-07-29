<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MetadatasTagsFixture
 *
 */
class MetadatasTagsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'metadata_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'tag_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'tag_key' => ['type' => 'index', 'columns' => ['tag_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['metadata_id', 'tag_id'], 'length' => []],
            'metadata_key' => ['type' => 'foreign', 'columns' => ['metadata_id'], 'references' => ['metadatas', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
            'tag_key' => ['type' => 'foreign', 'columns' => ['tag_id'], 'references' => ['tags', 'id'], 'update' => 'restrict', 'delete' => 'restrict', 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'metadata_id' => '612902d5-a562-4750-b1e5-f27d824fad44',
            'tag_id' => 'c405ce24-d8d6-466d-8798-c8ee9fc687dc'
        ],
    ];
}
