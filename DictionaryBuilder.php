<?php
require_once(dirname(__FILE__) . '/XMLParser.php');

class DictionaryBuilder
{
    private static $xmlZipLocation = 'http://www.ibiblio.org/webster/gcide_xml-0.51.zip';
    private static $localZipFile = 'gcide_xml-0.51.zip';
    private static $localDictionaryFile = 'dictionary.db';
    private $zipDownloaded = true;

    public function __construct()
    {
        if (file_exists(self::$localZipFile)) {
            // File is downloaded already,
            // might need to check file size
            // but that is another story
            echo 'Zip file which contains dictionaries is downloaded' . PHP_EOL;
            return;
        }

        // File to save the contents to
        $fp = fopen(self::$localZipFile, 'w+');

        // Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(self::$xmlZipLocation);

        curl_setopt($ch, CURLOPT_TIMEOUT, 50);

        // Give curl the file pointer so that it can write to it
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Get curl response
        if (!curl_exec($ch)) {
            echo 'Unable to download remote zip file to build dictionary';
            return;
        } else {
            $this->zipDownloaded = true;
        }

        // Done
        curl_close($ch);
    }

    public function buildDictionary()
    {
        if (file_exists(self::$localDictionaryFile)) {
            // Dictionary is built already,
            // might need to check file size
            // but that is another story
            echo 'Dictionary is built already' . PHP_EOL;
            return;
        }

        if (!$this->zipDownloaded) {
            echo 'Zip is not downloaded successful' . PHP_EOL;
            return;
        }

        // Extract downloaded zip file
        $zip = new ZipArchive();
        if ($zip->open(self::$localZipFile) === true) {
            if ($zip->extractTo('./')) {
                echo 'Zip file extracted successfully' . PHP_EOL;
                $zip->close();
            } else {
                echo 'Zip file was not extracted properly' . PHP_EOL;
                $zip->close();
                return;
            }
        }

        // Create DB to store list of words
        $db = new SQLite3('dictionary.db');
        $db->exec("pragma synchronous = off;");
        $db->exec("DROP TABLE IF EXISTS dictionary; CREATE TABLE dictionary (words STRING)");
        $db->exec("CREATE INDEX word_idx ON dictionary (words)");
        foreach (glob('./gcide_xml-0.51/xml_files/gcide_?.xml') as $xmlFile) {
            $parser = new XMLParser($xmlFile);
            $words = $parser->extractWords();
            foreach ($words as $word) {
                $sql = "INSERT INTO dictionary VALUES ('" . SQLite3::escapeString($word) . "')";
                $db->exec($sql);
            }
        }
        echo 'Dictionary was built successfully' . PHP_EOL;
        $db->close();
    }
}