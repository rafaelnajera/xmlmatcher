<?php

/*
 *  Copyright (C) 2016 Universität zu Köln
 *  
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *   
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *  
 */

namespace XmlMatcher;

use Matcher\Pattern;
use PHPUnit\Framework\TestCase;

/**
 * Description of XmlTokenTest
 *
 * @author Rafael Nájera <rafael.najera@uni-koeln.de>
 */
class XmlTokenTest extends TestCase
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
}
