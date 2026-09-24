<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\PackagesController Test Case
 */
class PackagesControllerTest extends TestCase
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
     * The index lists every package with its latest metadata description.
     */
    public function testIndex(): void
    {
        $this->get('/packages');

        $this->assertResponseOk();
        // Each package is rendered as a link to its view page.
        $this->assertResponseContains('/packages/view/11111111-1111-4111-8111-111111111111');
        $this->assertResponseContains('/packages/view/22222222-2222-4222-8222-222222222222');
        $this->assertResponseContains('/packages/view/33333333-3333-4333-8333-333333333333');
        // The latest (2.0.0) metadata description is shown for foo, not the older one.
        $this->assertResponseContains('Alpha package version two.');
        $this->assertResponseContains('Beta protocol analyzer.');
        $this->assertResponseContains('Gamma utility helper.');
        // Pagination counter is present.
        $this->assertResponseContains('record(s) out of 3 total');
    }

    /**
     * Searching filters the listing to matching packages only.
     */
    public function testIndexSearch(): void
    {
        $this->get('/packages?q=Beta');

        $this->assertResponseOk();
        $this->assertResponseContains('Beta protocol analyzer.');
        $this->assertResponseNotContains('Alpha package version two.');
        $this->assertResponseNotContains('Gamma utility helper.');
    }

    /**
     * Search matches associated tag names as well as package fields.
     */
    public function testIndexSearchByTag(): void
    {
        $this->get('/packages?q=protocol');

        $this->assertResponseOk();
        $this->assertResponseContains('Beta protocol analyzer.');
        $this->assertResponseNotContains('Gamma utility helper.');
    }

    /**
     * A search with no matches still renders successfully with an empty list.
     */
    public function testIndexSearchNoResults(): void
    {
        $this->get('/packages?q=doesnotexistanywhere');

        $this->assertResponseOk();
        $this->assertResponseNotContains('Alpha package version two.');
        $this->assertResponseNotContains('Beta protocol analyzer.');
        $this->assertResponseNotContains('Gamma utility helper.');
    }

    /**
     * The view renders package details, all metadata versions, tags and README.
     */
    public function testView(): void
    {
        $this->get('/packages/view/11111111-1111-4111-8111-111111111111');

        $this->assertResponseOk();
        $this->assertResponseContains('https://github.com/zeek/foo');
        // Both metadata versions are present (the selector toggles visibility).
        $this->assertResponseContains('<option value="1.0.0"');
        $this->assertResponseContains('<option value="2.0.0"');
        $this->assertResponseContains('Alpha package for scanning traffic.');
        $this->assertResponseContains('Alpha package version two.');
        // The "scan" tag links to its tag view.
        $this->assertResponseContains('/tags/view/dddddddd-0001-4000-8000-000000000001');
        // GitHub stat links are rendered.
        $this->assertResponseContains('https://github.com/zeek/foo/stargazers');
    }

    /**
     * The Package Checks / CI block is rendered from the package_ci JSON.
     */
    public function testViewRendersPackageChecks(): void
    {
        $this->get('/packages/view/22222222-2222-4222-8222-222222222222');

        $this->assertResponseOk();
        $this->assertResponseContains('Package Checks');
        $this->assertResponseContains('Build Zeek');
        $this->assertResponseContains('Dns Resolution');
    }

    /**
     * Viewing without an id redirects to the index.
     */
    public function testViewWithoutIdRedirectsToIndex(): void
    {
        $this->get('/packages/view');

        $this->assertRedirect(['controller' => 'Packages', 'action' => 'index']);
    }

    /**
     * Trailing junk after the id redirects to the canonical view URL.
     */
    public function testViewWithTrailingJunkRedirects(): void
    {
        $this->get('/packages/view/11111111-1111-4111-8111-111111111111/some-extra-noise');

        $this->assertRedirect([
            'controller' => 'Packages',
            'action' => 'view',
            '11111111-1111-4111-8111-111111111111',
        ]);
    }

    /**
     * An unknown id yields a 404.
     */
    public function testViewUnknownIdReturnsNotFound(): void
    {
        $this->get('/packages/view/00000000-0000-4000-8000-000000000000');

        $this->assertResponseCode(404);
    }
}
