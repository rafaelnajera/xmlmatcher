# XmlMatcher

Implements a [Matcher\Token](https://github.com/rafaelnajera/matcher) that matches XML elements out of an \XmlReader

## Installation 
Install the latest version with

```bash
$ composer require rafaelnajera/xmlmatcher
```

## Usage

Use \Matcher\Pattern and \Matcher\PatternMatcher with XmlToken objects.

XmlToken provides three factory methods:

```php
$token = XmlToken::elementToken('name');     // XML:  <name>
$token = XmlToken::endelementToken('name');  // XML: </name>
$token = XmlToken::textToken();              // XML: free text
```

Element tokens can also specify required attributes:

```php
$token = XmlToken::elementToken('name')
    ->withReqAttrs([ ['a', 'value'], ['b', '*']); // XML: <name a="value" b="whatever"> 
```