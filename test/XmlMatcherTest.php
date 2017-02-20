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
 * Description of XmlTokenTest
 *
 * @author Rafael Nájera <rafael@najera.ca>
 */
class XmlMatcherTest extends TestCase
{
    
    public function testSimple()
    {
        $pattern = (new Pattern())->withTokenSeries([
            XmlToken::elementToken('tei'),
            XmlToken::textToken(),
            XmlToken::endElementToken('tei')
        ]);
        $matcher = new XmlMatcher($pattern);
        
        $matcher->matchXmlString('<tei>Some test</tei>');
        $this->assertEquals(true, $matcher->matchFound());
        $this->assertEquals([ 'type' => \XmlReader::ELEMENT,
            'name' => 'tei',
            'attributes' => [],
            'text' => ''], $matcher->matched[0]);
        
        $matcher->matchXmlString('<other>Some test</other>');
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->matchXmlString('<tei><other/>Some test</tei>');
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->matchXmlString('<tei>Some test<other/></tei>');
        $this->assertEquals(false, $matcher->matchFound());
    }
    
    public function testReqAttributes()
    {
        $pattern = (new Pattern())->withTokenSeries([
            XmlToken::elementToken('test')->withReqAttrs([ ['r', 'yes']]),
            XmlToken::elementToken('other')->withReqAttrs([ ['n', '/.*/']]),
            XmlToken::endElementToken('test')
        ]);
        
        $matcher = new XmlMatcher($pattern);
        $matcher->matchXmlString('<test r="yes"><other n="doesntmatter"/></test>');
        $this->assertEquals(true, $matcher->matchFound());
       
        $matcher->matchXmlString('<test r="no"><other n="doesntmatter"/></test>');
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->matchXmlString('<test x="yes"><other n="doesntmatter"/></test>');
        $this->assertEquals(false, $matcher->matchFound());
    }
    
    public function testOptAttributes()
    {
        $pattern = (new Pattern())->withTokenSeries([
            XmlToken::elementToken('test')->withReqAttrs([ ['r', 'yes']]),
            XmlToken::elementToken('other')->withOptAttrs([ ['n', '/.*/'], ['x', '/^[a-z]+$/']]),
            XmlToken::endElementToken('test')
        ]);
        
        $matcher = new XmlMatcher($pattern);
        
        $matcher->matchXmlString('<test r="yes"><other n="doesntmatter"/></test>');
        $this->assertEquals(true, $matcher->matchFound());
        
        $matcher->matchXmlString('<test r="yes"><other/></test>');
        $this->assertEquals(true, $matcher->matchFound());
        
        $matcher->matchXmlString('<test r="yes"><other x="abcba"/></test>');
        $this->assertEquals(true, $matcher->matchFound());
        
        $matcher->matchXmlString('<test r="yes"><other x="abc123"/></test>');
        $this->assertEquals(false, $matcher->matchFound());
    }
    
    public function testSkipping()
    {
        $pattern = (new Pattern())->withTokenSeries([
            XmlToken::elementToken('tei'),
            XmlToken::textToken(),
            XmlToken::endElementToken('tei')
        ]);
        $matcher = new XmlMatcher($pattern);
        
        $matcher->matchXmlString('<!-- Some comment -->     <tei>Some test</tei>');
        $this->assertEquals(true, $matcher->matchFound());
        $this->assertEquals([ 'type' => \XmlReader::ELEMENT,
            'name' => 'tei',
            'attributes' => [],
            'text' => ''], $matcher->matched[0]);
        
        $matcher->matchXmlString('<other>Some test</other>');
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->matchXmlString('<tei><other/>Some test</tei>');
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->matchXmlString('<tei>Some test<other/></tei>');
        $this->assertEquals(false, $matcher->matchFound());
    }
}
