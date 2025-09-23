<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MetadatasTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MetadatasTable Test Case
 */
class MetadatasTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MetadatasTable
     */
    public $Metadatas;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.metadatas',
        'app.packages',
        'app.tags',
        'app.metadatas_tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();
        $config = TableRegistry::exists('Metadatas') ? [] : ['className' => MetadatasTable::class];
        $this->Metadatas = TableRegistry::get('Metadatas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() : void
    {
        unset($this->Metadatas);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findTagged method
     *
     * @return void
     */
    public function testFindTagged() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
