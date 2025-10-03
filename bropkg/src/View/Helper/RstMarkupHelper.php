<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Doctrine\RST\Parser;

/**
 * Rst markup helper
 *
 * Render .rst markup files in view templates.
 */
class RstMarkupHelper extends Helper
{
    protected $_parser;

    /**
     * Parse RST markup input to HTML.
     *
     * @param string $text RST markup to be parsed.
     * @return string
     */
    public function parse(string $text)
    {
        $parsed = $this->_getParser()->parse($text);
        return $parsed->render();
    }

    /**
     * Creates a static instance of the RST parser.
     */
    protected function _getParser()
    {
        if ( $this->_parser !== null ) {
            return $this->_parser;
        }

        $this->_parser = new Parser();
        return $this->_parser;
    }
}
