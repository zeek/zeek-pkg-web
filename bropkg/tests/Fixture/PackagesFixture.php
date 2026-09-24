<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PackagesFixture
 *
 * Schema is loaded from tests/schema.sql via the test bootstrap, so only
 * records are defined here. The three packages below are cross-referenced by
 * the Metadatas/Tags/MetadatasTags fixtures and are ordered so that the home
 * page "top" lists have a deterministic ranking:
 *
 *   subscribers_count (Top Watched):  bar (100) > foo (10) > baz (1)
 *   stargazers_count  (Top Starred):  foo (200) > baz (50) > bar (5)
 *   pushed_at         (Recent):       baz > foo > bar
 */
class PackagesFixture extends TestFixture
{
    public array $records = [
        [
            'id' => '11111111-1111-4111-8111-111111111111',
            'name' => 'zeek/foo',
            'author' => 'alice',
            'short_name' => 'foo',
            'url' => 'https://github.com/zeek/foo',
            'readme' => 'The foo package readme content.',
            'readme_name' => 'README.md',
            'subscribers_count' => 10,
            'stargazers_count' => 200,
            'open_issues_count' => 3,
            'forks_count' => 7,
            'pushed_at' => '2024-06-15 12:00:00',
            'created' => '2024-01-01 00:00:00',
            'modified' => '2024-06-15 12:00:00',
        ],
        [
            'id' => '22222222-2222-4222-8222-222222222222',
            'name' => 'zeek/bar',
            'author' => 'bob',
            'short_name' => 'bar',
            'url' => 'https://github.com/zeek/bar',
            'readme' => 'The bar package readme content.',
            'readme_name' => 'README.rst',
            'subscribers_count' => 100,
            'stargazers_count' => 5,
            'open_issues_count' => 0,
            'forks_count' => 1,
            'pushed_at' => '2024-02-01 08:30:00',
            'created' => '2024-01-02 00:00:00',
            'modified' => '2024-02-01 08:30:00',
        ],
        [
            'id' => '33333333-3333-4333-8333-333333333333',
            'name' => 'zeek/baz',
            'author' => 'alice',
            'short_name' => 'baz',
            'url' => 'https://github.com/zeek/baz',
            'readme' => null,
            'readme_name' => null,
            'subscribers_count' => 1,
            'stargazers_count' => 50,
            'open_issues_count' => 9,
            'forks_count' => 2,
            'pushed_at' => '2024-09-20 18:45:00',
            'created' => '2024-01-03 00:00:00',
            'modified' => '2024-09-20 18:45:00',
        ],
    ];
}
