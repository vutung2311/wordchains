<?php

namespace WordChains;

use SplPriorityQueue;
use SQLite3;

class WordChains
{

    /**
     * SQLite database file to get data from.
     *
     * @var null
     */
    private $dbFile = null;

    /**
     * SQLite3 instance to get data from database.
     *
     * @var null|string
     */
    private $dbClass = null;

    /**
     * SQLite3 object.
     *
     * @var null|SQLite3
     */
    private $dbObject = null;

    /**
     * Construct the word chain processor.
     *
     * @param null|string $dbFile Db file to get data from.
     * @param null|SQLite3 $dbClass SQLite3 database object.
     */
    public function __construct($dbFile = null, $dbClass = null)
    {
        if (null !== $dbClass && null !== $dbFile) {
            $this->dbFile = $dbFile;
            $this->dbClass = $dbClass;
            $this->dbObject = new $dbClass($this->dbFile);

            return $this;
        } else {
            return null;
        }
    }

    /**
     * Solve the word chains using the priority queue
     *
     * @param $startWord string
     * @param $endWord string
     *
     * @return array|mixed
     */
    public function solve($startWord, $endWord)
    {
        if (strlen($startWord) != strlen($endWord)) {
            return [];
        }

        $q = new SplPriorityQueue();
        $q->insert($startWord, self::computeMatch($startWord, $endWord));
        $hashMap = [];

        // Create the hash map to look up for shortest path
        while (!empty($q) && ($word = $q->extract()) != $endWord) {
            if (!in_array($word, $hashMap)) {
                foreach ($this->getAdjacency($word) as $nextWord) {
                    if (!array_key_exists($nextWord, $hashMap)) {
                        $hashMap[$nextWord] = $word;
                        $q->insert($nextWord, self::computeMatch($nextWord, $endWord));
                    }
                }
            }
        }

        // Determine the shortest path
        if (!array_key_exists($endWord, $hashMap)) {
            return [];
        }
        $word = $endWord;
        $path = [];

        while ($word != $startWord) {
            $path[] = $word;
            $word = $hashMap[$word];
        }

        $path[] = $startWord;

        return array_reverse($path);
    }

    /**
     * Return the number of identical character between two word
     *
     * @param string $targetWord Target word to compare to.
     * @param string $sourceWord Source word to compare from.
     * @return int
     */
    public static function computeMatch($targetWord, $sourceWord)
    {
        $diff = 0;
        for ($i = 0; $i < strlen($targetWord); $i++) {
            if ($targetWord[$i] != $sourceWord[$i]) {
                $diff++;
            }
        }
        return strlen($targetWord) - $diff;
    }

    /**
     * Get adjacent words of a specific word
     *
     * @param string $word Word to get adjacent words.
     * @return array
     */
    public function getAdjacency($word)
    {
        $wildCardList = [];
        $wordList = [];

        for ($i = 0; $i < strlen($word); $i++) {
            $wildCardList[] = substr($word, 0, $i) . '_' . substr($word, $i + 1);
        }

        foreach ($wildCardList as $wildCard) {
            $sql = sprintf(
                "SELECT * FROM dictionary WHERE words LIKE '%s' AND words <> '%s'",
                SQLite3::escapeString($wildCard),
                SQLite3::escapeString($word)
            );
            $result = $this->dbObject->query($sql);
            while ($row = $result->fetchArray()) {
                $wordList[] = $row[0];
            }
        }

        return $wordList;
    }
}