<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MetadatasTagsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MetadatasTagsTable Test Case
 */
class MetadatasTagsTableTest extends TestCase
{
    protected MetadatasTagsTable $MetadatasTags;

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
        $this->MetadatasTags = $this->getTableLocator()->get('MetadatasTags');
    }

    public function tearDown(): void
    {
        unset($this->MetadatasTags);
        parent::tearDown();
    }

    /**
     * The join table associates to both Metadatas and Tags on a composite key.
     */
    public function testInitialize(): void
    {
        $this->assertSame('metadatas_tags', $this->MetadatasTags->getTable());
        $this->assertSame(['metadata_id', 'tag_id'], $this->MetadatasTags->getPrimaryKey());
        $this->assertTrue($this->MetadatasTags->hasAssociation('Metadatas'));
        $this->assertTrue($this->MetadatasTags->hasAssociation('Tags'));
    }

    /**
     * buildRules() rejects rows whose metadata_id/tag_id do not exist.
     */
    public function testExistsInRulesRejectMissingReferences(): void
    {
        $link = $this->MetadatasTags->newEntity([
            'metadata_id' => '00000000-0000-4000-8000-0000000000ff',
            'tag_id' => '00000000-0000-4000-8000-0000000000ee',
        ]);

        $this->assertFalse($this->MetadatasTags->save($link));
        $errors = $link->getErrors();
        $this->assertArrayHasKey('metadata_id', $errors);
        $this->assertArrayHasKey('tag_id', $errors);
    }

    /**
     * A link between an existing metadata and tag saves successfully.
     */
    public function testExistsInRulesAcceptValidReferences(): void
    {
        $link = $this->MetadatasTags->newEntity([
            // baz's metadata, not yet linked to any tag.
            'metadata_id' => 'cccccccc-0001-4000-8000-000000000001',
            'tag_id' => 'dddddddd-0002-4000-8000-000000000002',
        ]);

        $this->assertNotFalse($this->MetadatasTags->save($link));
    }
}
