<?php

require_once('DictionaryBuilder.php');
require_once('FileDownloader.php');
require_once('FileManager.php');
require_once('WordChains.php');
require_once('WordExtractor.php');

use WordChains\DictionaryBuilder;
use WordChains\FileDownloader;
use WordChains\FileManager;
use WordChains\WordChains;
use WordChains\WordExtractor;

class WordChainsTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test object
     *
     * @var WordChains
     */
    private $object;

    public function setUp()
    {
        $fileDownloader = new FileDownloader();
        $wordExtractor = new WordExtractor();
        $zipExtractor = new ZipArchive();
        $fileManager = new FileManager();
        $dictionaryBuilder = new DictionaryBuilder(
            $fileDownloader,
            $wordExtractor,
            $zipExtractor,
            SQLite3::class,
            $fileManager
        );
        $this->object = new WordChains(
            $dictionaryBuilder->buildDictionary(),
            SQLite3::class
        );
    }

    public function testSymmetry()
    {
        // Act
        $b = $this->object->solve('cat', 'dog');
        $c = $this->object->solve('dog', 'cat');

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testCatDog() {
        // Act
        $b = $this->object->solve('cat', 'dog');
        $c = ['cat', 'cot', 'cog', 'dog'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testRubyCode() {
        // Act
        $b = $this->object->solve('ruby', 'code');
        $c = ['ruby', 'rubs', 'robs', 'rods', 'rode', 'code'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testLeadGold() {
        // Act
        $b = $this->object->solve('lead', 'gold');
        $c = ['lead', 'load', 'goad', 'gold'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }

    public function testJavaCode() {
        // Act
        $b = $this->object->solve('java', 'code');
        $c = ['java', 'lava', 'lave', 'cave', 'cove', 'code'];

        // Assert
        $this->assertEquals(implode(' ', $c), implode(' ', $b));
    }
}