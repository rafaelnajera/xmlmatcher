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

use \Matcher\Token;

/**
 * Implementation of \Matcher\Token to match XML elements
 * out of an \XmlReader parser
 *
 * @author Rafael Nájera <rafael@najera.ca>
 */
class XmlToken implements Token
{
   
    private $type;
    private $name;
    private $requiredAttributes;
    private $optionalAttributes;
    
    /**
     * Constructor
     *
     * @param int $type Xml type => XmlReader->nodeType
     * @param string $name => XmlReader->name
     */
    public function __construct($type, string $name = '/.*/')
    {
        $this->type = $type;
        $this->name = $name;
        $this->requiredAttributes = [];
        $this->optionalAttributes = [];
    }
    
    /**
     * Creates a copy of the XmlToken with required attributes
     *
     * @param array $attrs
     * @return \XmlMatcher\XmlToken
     */
    public function withReqAttrs(array $attrs)
    {
        $copy = clone $this;
        foreach ($attrs as $atr) {
            $copy->requiredAttributes[]  = $atr;
        }
        return $copy;
    }
    
    /**
     * Creates a copy of the XmlToken with optional attributes
     *
     * @param array $attrs
     * @return \XmlMatcher\XmlToken
     */
    public function withOptAttrs(array $attrs)
    {
        $copy = clone $this;
        foreach ($attrs as $atr) {
            $copy->optionalAttributes[]  = $atr;
        }
        return $copy;
    }
    
    /**
     * Matches the token with an XmlReader
     *
     * @param XmlReader $reader
     * @return boolean
     */
    public function matches($reader)
    {
        if (!$this->matchValue($this->type, $reader->nodeType)) {
            return false;
        }
        
        if (!$this->matchValueWithRegexp($this->name, $reader->name)) {
            return false;
        }
        if (!$this->matchRequiredAttributes($reader)) {
            return false;
        }
        if (!$this->matchOptionalAttributes($reader)) {
            return false;
        }
        return true;
    }
    
    /**
     * Returns an array with information about a matched
     * token.
     *
     * @param XmlReader $reader
     * @return array
     */
    public function matched($reader)
    {
        return [
            'type'=> $this->type,
            'name'=> $this->name,
            'attributes' => $this->getAttributesFromReader($reader),
            'text' => $this->getTextFromReader($reader)
                ];
    }
    
    /**
     * Converts the token a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return "Type: $this->type, Name: $this->name";
    }
    
    /**
     * Converts an input token to a string representation
     *
     * @param XmlReader $reader
     * @return string
     */
    public function inputToString($reader)
    {
        return "Type: $reader->nodeType, Name: $reader->name";
    }
    
    //
    // Factory Functions
    //
    public static function elementToken($name)
    {
        return new XmlToken(\XMLReader::ELEMENT, $name);
    }
    
    public static function endElementToken($name)
    {
        return new XmlToken(\XMLReader::END_ELEMENT, $name);
    }
    
    public static function textToken()
    {
        return new XmlToken(\XMLReader::TEXT);
    }
    
    //
    // Utility Functions
    //
    
    private function matchValue($cond, $value)
    {
        return $cond === $value;
    }
    
    private function matchValueWithRegexp($cond, $value)
    {
        if (preg_match('/^\/.*\//', $cond)) {
            return preg_match($cond, $value);
        }
        if ($cond === $value) {
            return true;
        }
        return false;
    }
    
    private function matchRequiredAttributes(\XMLReader $reader)
    {
        // Attributes only make sense in xml elements!
        if ($this->type !== \XMLReader::ELEMENT) {
            return true;
        }
        foreach ($this->requiredAttributes as $atr) {
            $value = $reader->getAttribute($atr[0]);
            if (is_null($value)) {
                return false;
            }
            if (!$this->matchValueWithRegexp($atr[1], $value)) {
                return false;
            }
        }
        return true;
    }
    
    private function matchOptionalAttributes(\XMLReader $reader)
    {
        // Attributes only make sense in xml elements!
        if ($this->type !== \XMLReader::ELEMENT) {
            return true;
        }
        
        foreach ($this->optionalAttributes as $atr) {
            $value = $reader->getAttribute($atr[0]);
            // return false if the attribute exists and does not match
            // the given value/regexp
            if (!is_null($value) && !$this->matchValueWithRegexp($atr[1], $value)) {
                return false;
            }
        }
        return true;
    }
    
    private function getAttributesFromReader(\XMLReader $reader)
    {
        $attributes = [];
        foreach ($this->requiredAttributes as $attr) {
            $attributes[$attr[0]]=$reader->getAttribute($attr[0]);
        }
        
        foreach ($this->optionalAttributes as $attr) {
            $attributes[$attr[0]]=$reader->getAttribute($attr[0]);
        }
        return $attributes;
    }
    
    private function getTextFromReader(\XMLReader $reader)
    {
        if ($reader->nodeType !== \XMLReader::TEXT) {
            return '';
        }
        
        return $reader->readString();
    }
}
