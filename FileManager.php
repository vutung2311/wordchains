<?php

namespace WordChains;

/**
 * File manager class.
 */
class FileManager
{
    public function rm($path = null) {
        if (null !== $path) {
            if (is_dir($path)) {
                foreach(glob($path . '*', GLOB_MARK) as $childPath) {
                    $this->rm($childPath);
                }
                rmdir($path);
            } else if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}