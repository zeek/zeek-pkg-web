<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Gregwar\RST\Parser;

/**
 * Rst markup helper
 *
 * Render .rst markup files in view templates.
 */
class RstMarkupHelper extends Helper
{
  /**
   * Parse RST markup input to HTML.
   *
   * @param string $input RST markup to be parsed.
   * @return bool|string
   */
  public function transform($input)
  {
      if (!is_string($input)) {
          return false;
      }

      if (!isset($this->parser)) {
          $this->parser = new \Gregwar\RST\Parser;
      }

      return $this->parser->parse($input);
  }
}
