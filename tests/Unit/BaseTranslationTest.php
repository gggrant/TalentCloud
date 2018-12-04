<?php

namespace Tests\Unit;

use Tests\TestCase;

abstract class BaseTranslationTest extends TestCase
{
    protected $rootLangDir = 'resources/lang';

    /**
     * Return an array of all Lang files in directory tree
     * @param  string $dir directory to start searching in
     */
    protected function getAllFilesInDirectoryTree($dir) {
        $dir = trim($dir, '/');
        $filesAndDirectories = scandir($dir);
        $files = [];
        foreach($filesAndDirectories as $fileName) {
            $path = implode('/', [$dir, $fileName]);
            if ($fileName === '.' || $fileName === '..') {
                //Do nothing
            } elseif (is_dir($path)) {
                $files = array_merge($files, $this->getAllFilesInDirectoryTree($path));
            } else {
                array_push($files, $path);
            }
        }
        return $files;
    }

    protected function removePrefix($str, $prefix) {
        if (substr($str, 0, strlen($prefix)) == $prefix) {
            $str = substr($str, strlen($prefix));
        }
        return $str;
    }

    protected function removeFiletype($filename) {
        return preg_replace('/\\.[^.\\s]+$/', '', $filename);
    }

    protected function formatLangFilenames($langFiles, $prefix) {
        $output = [];
        foreach($langFiles as $file) {
            $relativeName = $this->removePrefix($file, $prefix);
            $relativeName = $this->removeFiletype($relativeName);
            $relativeName = trim($relativeName, '/');
            array_push($output, $relativeName);
        }
        return $output;
    }

    protected function getLangFilenames($lang) {
        $dir = implode('/', [$this->rootLangDir, $lang]);
        $langFiles = $this->getAllFilesInDirectoryTree($dir);
        $formatedLangFiles = $this->formatLangFilenames($langFiles, $dir);
        return $formatedLangFiles;
    }

    protected function getAllLangFilenames() {
        $langs = ['en', 'fr'];
        $output = [];
        foreach($langs as $lang) {
            $output = array_merge($output, $this->getLangFilenames($lang));
        }
        return $output;
    }
}
// instead of checking if specific files exists, check if every file in the lang folder returns an array. Do it for french and english at ghe same time no seperate test needed.
//
//  Prefix: Write a function, make it a function that returns an array of everything that you could use to pass into Lang::get. (return auth, manager/applicant, everything under resources/lang/en. For now.)
 // Loop through that array and make sure everything is passable into Lang::get and use that for other tests.

/*---
 *
 * 1) Use Scandir to get everything in the folder and loop through them. (same function as step 7) (its a recursive function, if its a file, turn that into a string that we can pass into Lang::get, if its a folder, we need to call this function again using that folder as a location.
 * 2) If its a file, you need to use the name of the file, and pass it to lang::get.
 * 3) and then use Assert InternalType array. The Assert is to check what comes back from Lang::get. Its possible Lang::get causes an exception, but will show up in tests (probably)
 * 4) Resources/Lang/EN or FR is where you start looking.
 * 5) Function needs to take a second argument, what am I going to prefix the filename with(arugment 1 is the directory you are looking in), for the Lang::get funcction, start with empty string. (argument 2 is an empty string. ''),
 * 6) Check Applicant Profile Controller line 43 for a working example of Lang::get.
 *
 * ---
 *
 * Put the test in one function, but have the function contain two starting points. One for EN/ one for FR.
 *
 */
