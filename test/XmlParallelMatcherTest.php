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

use Matcher\Pattern;
use PHPUnit\Framework\TestCase;

/**
 * Description of XmlParallelMatcherTest
 *
 * @author Rafael Nájera <rafael.najera@uni-koeln.de>
 */
class XmlParallelMatcherTest extends TestCase
{
    
    public function testSimple()
    {
        
        $textPattern = (new Pattern())
            ->withTokenSeries([
                XmlToken::textToken()
        ]);
        
        $sicPattern = (new Pattern())
            ->withTokenSeries([
                XmlToken::elementToken('sic'), 
                XmlToken::textToken(), 
                XmlToken::endElementToken('sic')
            ]);
        
        $rubricPattern = (new Pattern())
            ->withTokenSeries([
                XmlToken::elementToken('hi')->withReqAttrs([['rend', 'rubric']]),
                XmlToken::textToken(),
                XmlToken::endElementToken('hi')
        ]);
        
        $initialPattern = (new Pattern())
            ->withTokenSeries([
                XmlToken::elementToken('hi')->withReqAttrs([['rend', 'initial']]),
                XmlToken::textToken(),
                XmlToken::endElementToken('hi')
        ]);
        
        $pmatcher = new XmlParallelMatcher([$textPattern, $sicPattern, $rubricPattern, $initialPattern]);
        
        $pmatcher->matchXmlString('<sic>Leks</sic> say this is a line');
        $this->assertEquals(2, $pmatcher->numMatches());
        $this->assertEquals('Leks', $pmatcher->matched[0][1]['text']);
        $this->assertEquals(' say this is a line', $pmatcher->matched[1][0]['text']);
        
        $pmatcher->matchXmlString('<hi rend="rubric">Lets</hi> say this is a line');
        $this->assertEquals(2, $pmatcher->numMatches());
        $this->assertEquals('Lets', $pmatcher->matched[0][1]['text']);
        $this->assertEquals(' say this is a line', $pmatcher->matched[1][0]['text']);
        
        // Bad XML
        $result1 = $pmatcher->matchXmlString('This is a line with <sic>bad XML</zic>');
        $this->assertFalse($result1);
        $this->assertEquals(XmlParallelMatcher::ERROR_XML_ERROR, $pmatcher->errorCode);
        $this->assertNotFalse($pmatcher->xmlError);
        
        // bad tag
        $result2 = $pmatcher->matchXmlString('This is a line with a <wrong>tag</wrong> and more text');
        $this->assertFalse($result2);
        $this->assertEquals(XmlParallelMatcher::ERROR_MATCH_ERROR, $pmatcher->errorCode);
        $this->assertFalse($pmatcher->xmlError);
        $this->assertEquals(1, $pmatcher->numMatches());
        $this->assertEquals('<wrong>tag</wrong>', $pmatcher->matchErrorElement);
        
        // another bad tag
        $result3 = $pmatcher->matchXmlString('This is a line with a <hi>bad hi tag</hi>');
        $this->assertFalse($result3);
        $this->assertEquals(XmlParallelMatcher::ERROR_MATCH_ERROR, $pmatcher->errorCode);
        $this->assertFalse($pmatcher->xmlError);
        $this->assertEquals(1, $pmatcher->numMatches());
        $this->assertEquals('<hi>bad hi tag</hi>', $pmatcher->matchErrorElement);
    }
    
    
}
