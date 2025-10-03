<?php
namespace App\View\Helper;

use Cake\View\Helper;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Markdown Helper
 *
 * This massages Markdown text to make the outcome suitable for rendering
 * on our site. The expectation is that the output is again transformed
 * by a markdown renderer -- see Template/Packages/view.ctp.
 */
class MarkdownHelper extends Helper
{
    protected $_converter;

    /**
     * Parse Markdown text to HTML.
     */
    public function parse(string $text, string $url)
    {
        $canonified = $this->canonify($text, $url);
        $parsed = $this->_getParser()->convert($text);
        return $this->fix_img_urls($parsed, $url);
    }

    private function _getParser()
    {
        if ( $this->_converter !== null ) {
            return $this->_converter;
        }

        // The 'heading_permalink' bit below adds anchor links to all heading
        // elements in the markdown, similar to how GitHub does. This allows
        // linking to those headers from other places in the README.
        $config = [
            'default_attributes' => [
                Table::class => [
                    'class' => 'table table-bordered'
                ],
            ],
            'heading_permalink' => [
                'id_prefix' => '',
                'aria_hidden' => true,
                'symbol' => '',
            ]
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new DefaultAttributesExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $this->_converter = new MarkdownConverter($environment);
        return $this->_converter;
    }

    private function make_element_absolute_url(string $page_url, string $elem_path): string {
        $url_path = parse_url($elem_path, PHP_URL_PATH);

        // Github markdown links can have descriptive text after the link itself,
        // separated by a space from the link. parse_url and path_info don't
        // know this and so they return the whole thing. Split off just the first
        // part as the extension.
        $extension = explode(" ", pathinfo($url_path, PATHINFO_EXTENSION))[0];

        if (strcasecmp($extension, "jpg") == 0 ||
            strcasecmp($extension, "jpeg") == 0 ||
            strcasecmp($extension, "png") == 0 ||
            strcasecmp($extension, "gif") == 0 ||
            strcasecmp($extension, "webp") == 0) {
            $page_url = preg_replace('|://github\.com/|', '://raw.githubusercontent.com/', $page_url);
            return $page_url . "/master/" . $elem_path;
        }

        return $page_url . "/blob/master/" . $elem_path;
    }

    private function fix_img_urls(string $html, string $page_url) {
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        $imgs = $doc->getElementsByTagName("img");
        foreach ($imgs as $img) {
            if ( strpos($img->getAttribute("src"), "raw.githubusercontent.com") == false ) {
                $img_path = $this->make_element_absolute_url($page_url, $img->getAttribute("src"));
                $img->setAttribute("src", $img_path);
            }
        }

        return $doc->saveHTML();
    }

    /**
     * Canonify Markdown content.
     *
     * @param string $input Markdown to be parsed.
     * @param string $url The package URL for this Markdown.
     * @return bool|string
     */
    private function canonify(string $input, string $url)
    {
        // TODO: It'd really be better do this when parsing the package data instead
        // of doing this every time we load the package page.

        if (strcasecmp(parse_url($url, PHP_URL_HOST), "github.com") == 0) {
            /* If the url passed is to github.com, we want to rewrite the relative
             * links so that they link to github. For images, we want to link to
             * raw.githubcontent.com so it will load the actual image instead. Other
             * links can be to the repo view. There isn't a good way to look up the
             * MIME type for the paths, so just assume anything ending in .jpg or
             * .png is an image, and anything else isn't.
             */
            $input = preg_replace_callback(
                '|\]\(([^)]*)\)|',
                function ($matches) use ($url) {
                    // Check for whether the URL in this has a scheme on it, something
                    // like https://. Anything without that, we can consider a relative
                    // link.
                    if (empty(parse_url($matches[1], PHP_URL_SCHEME)) && !str_starts_with($matches[1], "#")) {
                        $elem_url = $this->make_element_absolute_url($url, $matches[1]);
                        return '](' . $elem_url . ')';
                    }

                    /* This already links to an absolute URL: keep as-is. */
                    return $matches[0];
                },
                $input
            );
        } else {
            /* For other sites simply remove the link, keeping its anchor text. We
             * don't know where to direct the link.
             */
            $input = preg_replace_callback(
                '|\[([^\]]*)\]\([^)]*\)|',
                function ($matches) {
                    return $matches[1];
                },
                $input
            );
        }

        return $input;
    }
}
