<?php
namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Markdown canonifier
 *
 * This massages Markdown text to make the outcome suitable for rendering
 * on our site. The expectation is that the output is again transformed
 * by a markdown renderer -- see Template/Packages/view.ctp.
 */
class MarkdownCanonifierHelper extends Helper
{
  /**
   * Canonify Markdown content.
   *
   * @param string $input Markdown to be parsed.
   * @param string $url The package URL for this Markdown.
   * @return bool|string
   */
  public function transform($input, $url)
  {
      if (!is_string($input)) {
          return false;
      }
      if (strcasecmp(parse_url($url, PHP_URL_HOST), "github.com") == 0) {
          /* The package resides on Github. Replace relative links with a Github
           * blob reference into the source tree. Github does the same, with one
           * difference: Github knows the default branch, which the blob
           * reference requires. We do not know that branch, but Github
           * redirects an unknown branch to the default one. So we assume
           * "main", and hope for the redirect to work out if needed.
           */
          $input = preg_replace_callback(
              '|\]\(([^)]*)\)|',
              function ($matches) use ($url) {
                  if (empty(parse_url($matches[1], PHP_URL_SCHEME))) {
                      return "](" . $url . "/blob/main/" . $matches[1] . ")";
                  }

                  /* This already links to a URL: keep as-is. */
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
