<?php
namespace App\View\Helper;

use Cake\View\Helper;
use League\CommonMark\CommonMarkConverter;

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
  public function parse($text)
  {
      return $this->_getParser()->convert($text);
  }

  protected function _getParser()
  {
      if ( $this->_converter !== null ) {
         return $this->_converter;
      }
      $this->_converter = new CommonMarkConverter();
      return $this->_converter;
  }

  /**
   * Canonify Markdown content.
   *
   * @param string $input Markdown to be parsed.
   * @param string $url The package URL for this Markdown.
   * @return bool|string
   */
  public function canonify($input, $url)
  {
      // TODO: It'd really be better do this when parsing the package data instead
      // of doing this every time we load the package page.

      if (!is_string($input)) {
          return false;
      }
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
                  if (empty(parse_url($matches[1], PHP_URL_SCHEME))) {
                      $url_path = parse_url($matches[1], PHP_URL_PATH);

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
                          $url = preg_replace('|://github\.com/|', '://raw.githubusercontent.com/', $url);
                          return "](" . $url . "/master/" . $matches[1] . ")";
                      }

                      return "](" . $url . "/blob/master/" . $matches[1] . ")";
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
