<?php

namespace WordChains;
use SQLite3;
use ZipArchive;

/**
 * Dictionary builder class
 */
class DictionaryBuilder
{
    /**
     * Path of local sqlite dictionary database.
     *
     * @var string
     */
    private $localDictionaryPath = 'dictionary.db';

    /**
     * File downloader object.
     *
     * @var null|FileDownloader
     */
    private $fileDownloader = null;

    /**
     * Word extractor object.
     *
     * @var null|WordExtractor
     */
    private $wordExtractor = null;

    /**
     * Zip extractor class.
     *
     * @var null|ZipArchive
     */
    private $zipExtractor = null;

    /**
     * SQLite3 object to build sqlite database.
     *
     * @var null|SQLite3
     */
    private $sqliteDb = null;

    /**
     * Class string of SQLite3
     *
     * @var null|string
     */
    private $sqliteClass = null;

    /**
     * File manager object
     *
     * @var null|FileManager
     */
    private $fileManager = null;

    /**
     * Construct method.
     *
     * @param FileDownloader|null $fileDownloader File downloader object.
     * @param WordExtractor|null $wordExtractor Word extractor object.
     * @param ZipArchive|null $zipExtractor Zip extractor object.
     * @param null|string $sqliteClass Sqlite3 class to use.
     * @param null|FileManager $fileManager File manager class to delete extracted directory.
     */
    public function __construct(
        FileDownloader $fileDownloader = null,
        WordExtractor $wordExtractor = null,
        ZipArchive $zipExtractor = null,
        $sqliteClass = null,
        FileManager $fileManager = null
    )
    {
        if (null !== $fileDownloader
            && null !== $wordExtractor
            && null !== $zipExtractor
            && null !== $sqliteClass
            && null !== $fileManager
        ) {
            $this->fileDownloader = $fileDownloader;
            $this->wordExtractor = $wordExtractor;
            $this->zipExtractor = $zipExtractor;
            $this->sqliteClass = $sqliteClass;
            $this->fileManager = $fileManager;
            
            return $this;
        } else {
            return null;
        }
    }

    public function buildDictionary()
    {
        if (file_exists($this->localDictionaryPath) && filesize($this->localDictionaryPath) > 0) {
            return $this->localDictionaryPath;
        }

        $this->sqliteDb = new $this->sqliteClass($this->localDictionaryPath);

        $downloadedFile = $this->fileDownloader->downloadFile();

        // Extract downloaded zip file
        if ($this->zipExtractor->open($downloadedFile) === true) {
            $extractedResult = $this->zipExtractor->extractTo('./');
            $this->zipExtractor->close();
            if (!$extractedResult)
                return false;
        }

        // Create DB to store list of words
        $this->sqliteDb->exec("pragma synchronous = off;");
        $this->sqliteDb->exec("DROP TABLE IF EXISTS dictionary; CREATE TABLE dictionary (words STRING)");
        $this->sqliteDb->exec("CREATE INDEX word_idx ON dictionary (words)");
        foreach (glob('./gcide_xml-0.51/xml_files/gcide_?.xml') as $xmlFile) {
            $words = $this->wordExtractor->setXmlFile($xmlFile)->extractWords();
            foreach ($words as $word) {
                $sql = sprintf("INSERT INTO dictionary VALUES ('%s')", SQLite3::escapeString($word));
                $this->sqliteDb->exec($sql);
            }
        }

        // Remove extracted directory
        $this->fileManager->rm('./gcide_xml-0.51/');

        return $this->localDictionaryPath;
    }
}