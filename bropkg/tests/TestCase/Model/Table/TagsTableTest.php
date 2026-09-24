<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TagsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TagsTable Test Case
 */
class TagsTableTest extends TestCase
{
    protected TagsTable $Tags;

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
        $this->Tags = $this->getTableLocator()->get('Tags');
    }

    public function tearDown(): void
    {
        unset($this->Tags);
        parent::tearDown();
    }

    /**
     * The Metadatas belongsToMany association is configured.
     */
    public function testInitialize(): void
    {
        $this->assertSame('tags', $this->Tags->getTable());
        $this->assertSame('name', $this->Tags->getDisplayField());
        $this->assertTrue($this->Tags->hasAssociation('Metadatas'));
        $this->assertSame(
            'Cake\ORM\Association\BelongsToMany',
            get_class($this->Tags->getAssociation('Metadatas'))
        );
    }

    /**
     * A tag can be loaded with its associated metadata records.
     */
    public function testContainMetadatas(): void
    {
        $tag = $this->Tags->get(
            'dddddddd-0001-4000-8000-000000000001',
            contain: ['Metadatas']
        );

        $this->assertSame('scan', $tag->name);
        // "scan" is linked to both of foo's metadata versions.
        $this->assertCount(2, $tag->metadatas);
    }
}
