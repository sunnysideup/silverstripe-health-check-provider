<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Files;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatFiles extends HealthCheckItemRunner
{
    private static $excluded_folders = [];

    private static $excluded_files = [
        'error-500.html',
        'error-404.html',
        // DIRECTORY_SEPARATOR . '_resampled',
        // DIRECTORY_SEPARATOR . '__',
        // DIRECTORY_SEPARATOR . '.',
    ];

    /**
     * get a list of files in the asset path
     * @return array
     */
    public function getCalculatedAnswer()
    {
        $finalArray = [];
        $arrayRaw = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->getAssetPath()),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($arrayRaw as $src) {
            $path = $src->getPathName();
            if ($this->excludeFileTest($path)) {
                continue;
            }
            if (is_dir($path)) {
                continue;
            }
            $folderName = basename(dirname($path));
            if ($this->excludeFolderTest($folderName)) {
                continue;
            }

            $finalArray[$path] = $path;
        }

        return $finalArray;
    }

    /**
     * return the location for assets
     * @return string
     */
    protected function getAssetPath(): string
    {
        $path = realpath(ASSETS_PATH);
        if ($path) {
            return $path;
        }
        user_error('Could not find asset path');
    }

    /**
     * get an extension of a file
     * @param  string $s
     *
     * @return string
     */
    protected function fileExtension(string $s): string
    {
        $n = strrpos($s, '.');

        return $n === false ? '' : substr($s, $n + 1);
    }

    /**
     * should the file be ignored
     * @param  string $path
     * @return bool
     */
    protected function excludeFileTest(string $path): bool
    {
        $listOfItemsToSearchFor = Config::inst()->get(self::class, 'excluded_files');
        foreach ($listOfItemsToSearchFor as $test) {
            $pos = strpos($path, $test);
            if ($pos !== false) {
                if (substr($path, $pos) === $test) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * should the folder be ignored
     * @param  string $folderName
     * @return bool
     */
    protected function excludeFolderTest(string $folderName): bool
    {
        $listOfItemsToSearchFor = Config::inst()->get(self::class, 'excluded_folders');
        foreach ($listOfItemsToSearchFor as $test) {
            if ($folderName === $test) {
                return true;
            }
        }

        return false;
    }
}
