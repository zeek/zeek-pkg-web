<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * The home page queries the Packages table for its counts and top lists.
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Packages',
        'app.Metadatas',
        'app.Tags',
        'app.MetadatasTags',
    ];

    /**
     * Restore the default debug level, since some tests toggle it.
     */
    public function tearDown(): void
    {
        Configure::write('debug', true);
        parent::tearDown();
    }

    /**
     * The root URL renders the home page successfully more than once.
     */
    public function testMultipleGet(): void
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->get('/');
        $this->assertResponseOk();
    }

    /**
     * The home page shows the package count and the "top" lists.
     */
    public function testHomePage(): void
    {
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('<html');
        // Package count link.
        $this->assertResponseContains('View List of 3 Packages');
        // Intro copy / external links.
        $this->assertResponseContains('Zeek Package Manager');
        // With only three packages, every package appears in the top-5 lists,
        // each linked to its view page.
        $this->assertResponseContains('/packages/view/11111111-1111-4111-8111-111111111111');
        $this->assertResponseContains('/packages/view/22222222-2222-4222-8222-222222222222');
        $this->assertResponseContains('/packages/view/33333333-3333-4333-8333-333333333333');
    }

    /**
     * /pages/home renders the same home template.
     */
    public function testDisplay(): void
    {
        $this->get('/pages/home');

        $this->assertResponseOk();
        $this->assertResponseContains('<html');
        $this->assertResponseContains('View List of 3 Packages');
    }

    /**
     * A missing template renders a 404 page in production mode.
     */
    public function testMissingTemplate(): void
    {
        Configure::write('debug', false);
        $this->get('/pages/not_existing');

        $this->assertResponseError();
        $this->assertResponseContains('Error');
    }

    /**
     * A missing template surfaces the MissingTemplateException in debug mode.
     */
    public function testMissingTemplateInDebug(): void
    {
        Configure::write('debug', true);
        $this->get('/pages/not_existing');

        $this->assertResponseFailure();
        $this->assertResponseContains('Missing Template');
        $this->assertResponseContains('not_existing');
    }

    /**
     * Directory traversal attempts are forbidden.
     */
    public function testDirectoryTraversalProtection(): void
    {
        $this->get('/pages/../Layout/ajax');

        $this->assertResponseCode(403);
        $this->assertResponseContains('Forbidden');
    }
}
