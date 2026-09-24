<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MetadatasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MetadatasTable Test Case
 */
class MetadatasTableTest extends TestCase
{
    protected MetadatasTable $Metadatas;

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
        $this->Metadatas = $this->getTableLocator()->get('Metadatas');
    }

    public function tearDown(): void
    {
        unset($this->Metadatas);
        parent::tearDown();
    }

    /**
     * The Packages and Tags associations are configured.
     */
    public function testInitialize(): void
    {
        $this->assertSame('metadatas', $this->Metadatas->getTable());
        $this->assertTrue($this->Metadatas->hasAssociation('Packages'));
        $this->assertTrue($this->Metadatas->hasAssociation('Tags'));
        $this->assertSame(
            'Cake\ORM\Association\BelongsTo',
            get_class($this->Metadatas->getAssociation('Packages'))
        );
        $this->assertSame(
            'Cake\ORM\Association\BelongsToMany',
            get_class($this->Metadatas->getAssociation('Tags'))
        );
    }

    /**
     * A metadata row can be loaded with its owning package and tags.
     */
    public function testContainPackageAndTags(): void
    {
        $metadata = $this->Metadatas->get(
            'aaaaaaaa-0001-4000-8000-000000000001',
            contain: ['Packages', 'Tags']
        );

        $this->assertSame('1.0.0', $metadata->version);
        $this->assertSame('foo', $metadata->package->short_name);
        $this->assertCount(1, $metadata->tags);
        $this->assertSame('scan', $metadata->tags[0]->name);
    }
}
