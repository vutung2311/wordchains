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
     * @param SQLite3 $sqliteDb SQLite3 database object.
     * @param null|FileManager $fileManager File manager class to delete extracted directory.
     */
    public function __construct(
        FileDownloader $fileDownloader = null,
        WordExtractor $wordExtractor = null,
        ZipArchive $zipExtractor = null,
        SQLite3 $sqliteDb = null,
        FileManager $fileManager = null
    )
    {
        if (null !== $fileDownloader
            && null !== $wordExtractor
            && null !== $zipExtractor
            && null !== $sqliteDb
            && null !== $fileManager
        ) {
            $this->fileDownloader = $fileDownloader;
            $this->wordExtractor = $wordExtractor;
            $this->zipExtractor = $zipExtractor;
            $this->sqliteDb = $sqliteDb;
            $this->fileManager = $fileManager;

            return $this;
        } else {
            return null;
        }
    }

    public function buildDictionary()
    {
        // Check if dictionary is built yet.
        try {
            $wordsCount = $this->sqliteDb->querySingle('SELECT COUNT(*) FROM dictionary');
            if (null !== $wordsCount && $wordsCount > 0) {
                return $this->sqliteDb;
            }
        } catch (\Exception $exception) {}

        $downloadedFile = $this->fileDownloader->downloadFile();
        if (false === $downloadedFile) {
            return null;
        }

        // Extract downloaded zip file.
        try {
            if ($this->zipExtractor->open($downloadedFile) === false) {
                return null;
            }
            $extractedResult = $this->zipExtractor->extractTo('./');
            if (!$extractedResult) {
                return null;
            }
            $this->zipExtractor->close();
        } catch (\Exception $exception) {
            return null;
        }

        // Create DB to store list of words.
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

        // Remove extracted directory.
        $this->fileManager->rm('./gcide_xml-0.51/');

        return $this->sqliteDb;
    }
}