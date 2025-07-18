<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PackagesTable Test Case
 */
class PackagesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\PackagesTable
     */
    public $Packages;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.packages',
        'app.metadatas',
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
        $config = TableRegistry::exists('Packages') ? [] : ['className' => PackagesTable::class];
        $this->Packages = TableRegistry::get('Packages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() : void
    {
        unset($this->Packages);

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
}
