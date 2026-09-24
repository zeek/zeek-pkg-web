<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PackagesTable Test Case
 */
class PackagesTableTest extends TestCase
{
    protected PackagesTable $Packages;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Packages',
        'app.Metadatas',
        'app.Tags',
        'app.MetadatasTags',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->Packages = $this->getTableLocator()->get('Packages');
    }

    public function tearDown(): void
    {
        unset($this->Packages);
        parent::tearDown();
    }

    /**
     * The Metadatas association and Search behavior are configured.
     */
    public function testInitialize(): void
    {
        $this->assertSame('packages', $this->Packages->getTable());
        $this->assertTrue($this->Packages->hasAssociation('Metadatas'));
        $this->assertTrue($this->Packages->hasBehavior('Search'));
        $this->assertTrue($this->Packages->hasBehavior('Timestamp'));
    }

    /**
     * A package requires a non-empty name.
     */
    public function testValidationDefault(): void
    {
        $good = $this->Packages->newEntity([
            'name' => 'zeek/qux',
            'author' => 'carol',
            'short_name' => 'qux',
        ]);
        $this->assertEmpty($good->getErrors());

        $bad = $this->Packages->newEntity([
            'author' => 'carol',
            'short_name' => 'qux',
        ]);
        $this->assertArrayHasKey('name', $bad->getErrors());
    }

    /**
     * The search finder filters across package and associated fields, mirroring
     * how PackagesController::index() builds its query.
     */
    public function testSearchFinderMatchesDescription(): void
    {
        $query = $this->Packages
            ->find('search', search: ['q' => 'Beta'])
            ->leftJoinWith('Metadatas')
            ->leftJoinWith('Metadatas.Tags')
            ->groupBy('Packages.id');

        $results = $query->all()->toList();
        $this->assertCount(1, $results);
        $this->assertSame('bar', $results[0]->short_name);
    }

    /**
     * The search finder also matches associated tag names.
     */
    public function testSearchFinderMatchesTagName(): void
    {
        $query = $this->Packages
            ->find('search', search: ['q' => 'protocol'])
            ->leftJoinWith('Metadatas')
            ->leftJoinWith('Metadatas.Tags')
            ->groupBy('Packages.id');

        $shortNames = $query->all()->extract('short_name')->toList();
        $this->assertContains('bar', $shortNames);
        $this->assertNotContains('baz', $shortNames);
    }
}
