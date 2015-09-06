<?php
require_once dirname(__FILE__) . '/WordChains.php';

class WordChainsTest extends PHPUnit_Framework_TestCase
{
    public function testSymmetry()
    {
        // Arrange
        $a = new WordChains();

        // Act
        $b = $a->solve('cat', 'dog');
        $c = $a->solve('dog', 'cat');

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testCatDog() {
        // Arrange
        $a = new WordChains();

        // Act
        $b = $a->solve('cat', 'dog');
        $c = ['cat', 'cot', 'cog', 'dog'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testRubyCode() {
        // Arrange
        $a = new WordChains();

        // Act
        $b = $a->solve('ruby', 'code');
        $c = ['ruby', 'rubs', 'robs', 'rods', 'rode', 'code'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testLeadGold() {
        // Arrange
        $a = new WordChains();

        // Act
        $b = $a->solve('lead', 'gold');
        $c = ['lead', 'load', 'goad', 'gold'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testJavaCode() {
        // Arrange
        $a = new WordChains();

        // Act
        $b = $a->solve('java', 'code');
        $c = ['java', 'lava', 'lave', 'cave', 'cove', 'code'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }
}