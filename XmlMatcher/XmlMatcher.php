<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XmlMatcher;

use Matcher\Matcher;

/**
 * Description of XmlMatcher
 *
 * @author Rafael Nájera <rafael.najera@uni-koeln.de>
 */
class XmlMatcher extends Matcher
{
    const VERSION = '0.2';
    
    public function matchXmlReader(\XMLReader $reader)
    {
        while ($reader->read()) {
            $this->match($reader);
        }
    }
    
    public function matchXmlString(string $xmlString)
    {
        $reader = new \XMLReader();
        $reader->XML($xmlString);
        $this->reset();
        return $this->matchXmlReader($reader);
    }
}
