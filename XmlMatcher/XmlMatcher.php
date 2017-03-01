<?php

/*
 * The MIT License
 *
 * Copyright 2017 Rafael Nájera <rafael@najera.ca>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace XmlMatcher;

use Matcher\Matcher;

/**
 * Description of XmlMatcher
 *
 * @author Rafael Nájera <rafael@najera.ca>
 */
class XmlMatcher extends Matcher
{
    const VERSION = '0.4';
    
    const FAKE_TAG = 'xmlmatcherfaketagKVGYR';
    
    public function matchXmlReader(\XMLReader $reader, $skip = true)
    {
        while (XmlMatcher::advanceReader($reader, $skip)) {
            $this->match($reader);
        }
    }
    
    public function matchXmlString(string $xmlString, $skip = true)
    {
        // Add a fake tag to support fragmentary inner XML
        $modifiedString = XmlMatcher::addFakeTag($xmlString);
        $reader = new \XMLReader();
        $reader->XML($modifiedString);
        $this->reset();
        return $this->matchXmlReader($reader, $skip);
    }
    
    public static function advanceReader(\XMLReader $reader, $skip = true) 
    {
        $result = $reader->read();
        if (!$result) {
            return $result;
        }
        if ($skip) {
            while ($reader->nodeType === \XMLReader::SIGNIFICANT_WHITESPACE or 
                    $reader->nodeType === \XMLReader::COMMENT) {
                $result = $reader->read();
            }
        }
        if ($reader->nodeType === \XMLReader::ELEMENT && $reader->name === XmlMatcher::FAKE_TAG){
            return XmlMatcher::advanceReader($reader, $skip);
        }
        if ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->name === XmlMatcher::FAKE_TAG){
            return XmlMatcher::advanceReader($reader, $skip);
        }
        return $result;
    }
    
    public static function addFakeTag($xmlString){
        return '<' . self::FAKE_TAG . '>' . $xmlString . '</' . self::FAKE_TAG . '>';
    }
}
