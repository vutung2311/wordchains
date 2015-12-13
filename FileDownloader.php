<?php

namespace WordChains;

/**
 * Downloader class using curl of php
 */
class FileDownloader
{
    private $xmlZipUrl = 'http://www.ibiblio.org/webster/gcide_xml-0.51.zip';
    private $localZipPath = 'gcide_xml-0.51.zip';

    /**
     * @param null $xmlZipUrl Url of xml zip file to download.
     */
    public function __construct($xmlZipUrl = null)
    {
        if (null !== $xmlZipUrl) {
            $this->xmlZipUrl = $xmlZipUrl;
        }

        return $this;
    }

    /**
     * Download file to local.
     */
    public function downloadFile()
    {
        if (file_exists($this->localZipPath)) {
            return $this->localZipPath;
        }

        // File to save the contents to
        $fp = fopen($this->localZipPath, 'w+');

        // Here is the file we are downloading
        $ch = curl_init($this->xmlZipUrl);

        curl_setopt($ch, CURLOPT_TIMEOUT, 50);

        // Give curl the file pointer so that it can write to it
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Get curl response
        if (!curl_exec($ch)) {
            $returnValue = null;
        } else {
            $returnValue = $this->localZipPath;
        }

        // Done
        curl_close($ch);

        return $returnValue;
    }
}