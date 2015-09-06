<?php

class XMLParser
{
    private $xmlData = null;

    public function __construct($xmlFile)
    {
        $this->xmlData = @file_get_contents($xmlFile);
    }

    public function extractWords()
    {
        if ($this->xmlData) {
            $wordList = [];
            preg_match_all("/<p>(.*?)<\/p>/s", $this->xmlData, $records);
            $records = isset($records[0]) ? $records[0] : [];
            if ($records) {
                foreach ($records as $record) {
                    preg_match_all("/<ent>(.*?)<\/ent>/", $record, $entities);
                    if ($entities[1]) {
                        foreach ($entities[1] as $entity) {
                            $word = strtolower(trim(html_entity_decode($entity)));
                            $wordList[] = $word;
                        }
                    }
                }
            }
            $wordList = array_unique($wordList);
            return $wordList;
        } else {
            return [];
        }
    }
}