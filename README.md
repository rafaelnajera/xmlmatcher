# XmlMatcher

Implements a [Matcher\Token] that matches XML elements out of an \XmlReader

## Installation 
It is recommended that you use [Composer](https://getcomposer.org/) to install XmlMatcher.

XmlMatcher requires PHP 7.0 or above to run.

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