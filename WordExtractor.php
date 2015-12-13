<?php

namespace WordChains;

/**
 * Xml parser class
 */
class WordExtractor
{
    public function __construct($xmlFile = null)
    {
        if (null !== $xmlFile && file_exists($xmlFile)) {
            $this->xmlData = @file_get_contents($xmlFile);
        }

        return $this;
    }

    /**
     * Set xml file to extract words from.
     *
     * @return $this
     */
    public function setXmlFile($xmlFile = null)
    {
        if (null !== $xmlFile && file_exists($xmlFile)) {
            $this->xmlData = @file_get_contents($xmlFile);
        }

        return $this;
    }


    /**
     * Extract words list from xml file.
     *
     * @return array
     */
    public function extractWords()
    {
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
    }
}