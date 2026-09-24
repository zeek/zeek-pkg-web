<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TagsController Test Case
 */
class TagsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Packages',
        'app.Metadatas',
        'app.Tags',
        'app.MetadatasTags',
    ];

    /**
     * The index lists all tags sorted by name, each linking to its view.
     */
    public function testIndex(): void
    {
        $this->get('/tags');

        $this->assertResponseOk();
        $this->assertResponseContains('scan');
        $this->assertResponseContains('protocol');
        $this->assertResponseContains('unused');
        $this->assertResponseContains('/tags/view/dddddddd-0001-4000-8000-000000000001');
        $this->assertResponseContains('record(s) out of 3 total');
    }

    /**
     * Viewing a tag shows the tag name and its related packages.
     */
    public function testView(): void
    {
        $this->get('/tags/view/dddddddd-0001-4000-8000-000000000001');

        $this->assertResponseOk();
        $this->assertResponseContains('scan');
        // "scan" is attached to foo's metadata, so foo is the only related package.
        $this->assertResponseContains('zeek/foo');
        $this->assertResponseContains('/packages/view/11111111-1111-4111-8111-111111111111');
        $this->assertResponseNotContains('zeek/bar');
    }

    /**
     * A tag with no associated metadata renders with no related packages.
     */
    public function testViewTagWithoutPackages(): void
    {
        $this->get('/tags/view/dddddddd-0003-4000-8000-000000000003');

        $this->assertResponseOk();
        $this->assertResponseContains('unused');
        $this->assertResponseNotContains('zeek/foo');
        $this->assertResponseNotContains('zeek/bar');
    }

    /**
     * Viewing without an id redirects to the index.
     */
    public function testViewWithoutIdRedirectsToIndex(): void
    {
        $this->get('/tags/view');

        $this->assertRedirect(['controller' => 'Tags', 'action' => 'index']);
    }

    /**
     * Trailing junk after the id redirects to the canonical view URL.
     */
    public function testViewWithTrailingJunkRedirects(): void
    {
        $this->get('/tags/view/dddddddd-0001-4000-8000-000000000001/extra');

        $this->assertRedirect([
            'controller' => 'Tags',
            'action' => 'view',
            'dddddddd-0001-4000-8000-000000000001',
        ]);
    }

    /**
     * An unknown id yields a 404.
     */
    public function testViewUnknownIdReturnsNotFound(): void
    {
        $this->get('/tags/view/00000000-0000-4000-8000-000000000000');

        $this->assertResponseCode(404);
    }
}
