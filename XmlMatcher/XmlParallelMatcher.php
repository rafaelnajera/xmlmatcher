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

/**
 * \Matcher\ParallelMatcher with utility functions for XML matching
 *
 * @author Rafael Nájera <rafael@najera.ca>
 */
class XmlParallelMatcher extends \Matcher\ParallelMatcher
{
    const ERROR_NO_ERROR = 0;
    const ERROR_XML_ERROR = 1;
    const ERROR_MATCH_ERROR = 2;
    
    public $errorCode;
    public $matchErrorElement;
    public $xmlError;
    
    public function __construct(array $patternArray = array()) {
        parent::__construct($patternArray);
        $this->resetInternalErrors();
        
    }
    
    public function matchXmlReader(\XMLReader $reader, $skip = true)
    {
        $this->resetXmlErrors();
        $this->resetInternalErrors();

        while (XmlMatcher::advanceReader($reader, $skip)) {
            $matchResult = $this->match($reader);
            if (!$matchResult) {
               $this->errorCode = self::ERROR_MATCH_ERROR;
               $this->matchErrorElement = $reader->readOuterXml();
               return false;
            }
        }
        return !$this->xmlErrorsFound();
    }
    
    public function matchXmlString(string $xmlString, $skip = true)
    {
        $this->resetXmlErrors();
        // Add a fake tag to support fragmentary inner XML
        $modifiedString = XmlMatcher::addFakeTag($xmlString);

        $reader = new \XMLReader();
        if (!$reader->XML($modifiedString)) {
            return false;
        }
        $this->reset();
        return $this->matchXmlReader($reader, $skip);
    }
    
    private function resetXmlErrors()
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $this->xmlError = false;
    }
    
    private function resetInternalErrors() 
    {
        $this->errorCode = self::ERROR_NO_ERROR;
        $this->matchErrorElement = false;
        $this->xmlError = false;
    }
    
    private function xmlErrorsFound()
    {
        $this->xmlError = libxml_get_last_error();
        if ($this->xmlError) {
            $this->errorCode = self::ERROR_XML_ERROR;
            return true;
        }
        return false;
    }
}
