<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MetadatasTagsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MetadatasTagsTable Test Case
 */
class MetadatasTagsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MetadatasTagsTable
     */
    public $MetadatasTags;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.metadatas_tags',
        'app.metadatas',
        'app.packages',
        'app.tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('MetadatasTags') ? [] : ['className' => MetadatasTagsTable::class];
        $this->MetadatasTags = TableRegistry::get('MetadatasTags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MetadatasTags);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
