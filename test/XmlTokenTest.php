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


require '../vendor/autoload.php';
require_once '../XmlMatcher/XmlToken.php';

use XmlMatcher\XmlToken;
use Matcher\Pattern;
use Matcher\PatternMatcher;

/**
 * Description of XmlTokenTest
 *
 * @author Rafael Nájera <rafael.najera@uni-koeln.de>
 */
class XmlTokenTest extends PHPUnit_Framework_TestCase {
    
    public function testSimple(){
        $pattern = (new Pattern())->withTokenSeries([ 
            XmlToken::elementToken('tei'), 
            XmlToken::textToken(),
            XmlToken::endElementToken('tei')
        ]);
        $matcher = new PatternMatcher($pattern);
        
        $reader = new XMLReader();
        $reader->XML('<tei>Some test</tei>');
        
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(true, $matcher->matchFound());
        
        $matcher->reset();
        $reader->XML('<other>Some test</other>');
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->reset();
        $reader->XML('<tei><other/>Some test</tei>');
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->reset();
        $reader->XML('<tei>Some test<other/></tei>');
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(false, $matcher->matchFound());
    }
    
    public function testReqAttributes(){
        $pattern = (new Pattern())->withTokenSeries([ 
            XmlToken::elementToken('test')->withReqAttrs([ ['r', 'yes']]), 
            XmlToken::elementToken('other')->withReqAttrs([ ['n', '*']]),
            XmlToken::endElementToken('test')
        ]);
        
        $matcher = new PatternMatcher($pattern);
        $reader = new XMLReader();
        $reader->XML('<test r="yes"><other n="doesntmatter"/></test>');
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(true, $matcher->matchFound());
        //print_r($p);
        
        $matcher->reset();
        $reader->XML('<test r="no"><other n="doesntmatter"/></test>');
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(false, $matcher->matchFound());
        
        $matcher->reset();
        $reader->XML('<test x="yes"><other n="doesntmatter"/></test>');
        while ($reader->read()){
            $matcher->match($reader);
        }
        $this->assertEquals(false, $matcher->matchFound());
        
       
    }
    
}
