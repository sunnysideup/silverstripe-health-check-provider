<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Files;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class WhatFiles extends HealthCheckItemRunner
{
    protected $allowedExtension = [];

    private static $not_real_file_substrings = [
        DIRECTORY_SEPARATOR . '_resampled',
        DIRECTORY_SEPARATOR . '__',
        DIRECTORY_SEPARATOR . '.',
    ];

    private static $excluded_folders = [
        '.protected',
    ];

    private static $excluded_files = [
        'error-500.html',
        'error-404.html',
        '.gitignore',
        '.htaccess',
        // DIRECTORY_SEPARATOR . '_resampled',
        // DIRECTORY_SEPARATOR . '__',
        // DIRECTORY_SEPARATOR . '.',
    ];

    // anything over half a megabyte may needs attention...
    private static $min_size_in_bytes = ((1024 * 1024) / 2);

    /**
     * get a list of files in the asset path
     * @return array
     */
    public function getCalculatedAnswer()
    {
        $this->allowedExtension = Config::inst()->get(File::class, 'allowed_extensions');
        $finalArray = [];
        $arrayRaw = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->getAssetPath()),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $count = 0;
        $sizeSum = 0;
        foreach ($arrayRaw as $src) {
            $path = $src->getPathName();
            if ($this->excludeFileTest($path)) {
                continue;
            }
            if (is_dir($path)) {
                continue;
            }
            $folderName = basename(dirname($path));
            if ($this->excludeFolderTest($path)) {
                continue;
            }
            if ($this->isCountableFile($path)) {
                $count++;
            }
            $size = filesize($path);
            $sizeSum += $size;
            if ($size > $this->Config()->get('min_size_in_bytes') || $this->invalidExtension($path)) {
                if (strpos($path, $this->getAssetPath()) === 0) {
                    $shortPath = str_replace($this->getAssetPath(), '', $path);
                    $finalArray[(string) $shortPath] = $size;
                } else {
                    $finalArray[(string) $path] = $size;
                }
            }
        }

        return [
            'Path' => ASSETS_DIR,
            'Files' => $finalArray,
            'Count' => [
                'FileSystem' => $count,
                'DB' => File::get()->count(),
            ],
            'Size' => $sizeSum,
        ];
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
        $listOfItemsToSearchFor = $this->Config()->get('excluded_files');
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
     * @param  string $path
     * @return bool
     */
    protected function excludeFolderTest(string $path): bool
    {
        $listOfItemsToSearchFor = $this->Config()->get('excluded_folders');
        foreach ($listOfItemsToSearchFor as $test) {
            $folder = DIRECTORY_SEPARATOR . trim($test, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $pathExtra = DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) .  DIRECTORY_SEPARATOR;
            if (stripos($pathExtra, $folder) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function invalidExtension(string $path): bool
    {
        return $this->validExtension($path) ? false : true;
    }

    protected function validExtension(string $path): bool
    {
        $extension = $this->fileExtension($path);
        if ($extension && in_array($extension, $this->allowedExtension, true)) {
            return true;
        }
        return false;
    }

    protected function isCountableFile($path): bool
    {
        $listOfItemsToSearchFor = $this->Config()->get('not_real_file_substrings');
        foreach ($listOfItemsToSearchFor as $test) {
            if (strpos(DIRECTORY_SEPARATOR . $path, $test)) {
                return false;
            }
        }

        return true;
    }
}
