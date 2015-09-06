<?php

class WordChains
{

    private $db = null;

    /**
     * Initialize the connection to the database
     */
    public function __construct()
    {
        $this->db = new SQLite3('dictionary.db', SQLITE3_OPEN_READONLY);
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
     * @param $firstWord
     * @param $secondWord
     * @return int
     */
    public static function computeMatch($firstWord, $secondWord)
    {
        $diff = 0;
        for ($i = 0; $i < strlen($firstWord); $i++) {
            if ($firstWord[$i] != $secondWord[$i]) {
                $diff++;
            }
        }
        return strlen($firstWord) - $diff;
    }

    /**
     * Get adjacent words of a specific word
     *
     * @param $word
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
            $sql = "SELECT * FROM dictionary
            WHERE words LIKE '" . SQLite3::escapeString($wildCard) . "'
            AND words <> '" . SQLite3::escapeString($word) . "'";
            $result = $this->db->query($sql);
            while ($row = $result->fetchArray()) {
                $wordList[] = $row[0];
            }
        }

        return $wordList;
    }
}