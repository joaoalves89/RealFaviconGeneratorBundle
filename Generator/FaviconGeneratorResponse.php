<?php

namespace VentureOakLabs\FaviconGeneratorBundle\Generator;

use \InvalidArgumentException;

/**
 * Parses the API response and abstracts the result.
 *
 * Extracted from https://github.com/RealFaviconGenerator/rfg-api-php-demo/blob/master/rfg_api_response.php .
 *
 * @author João Alves <jalves@ventureoak.com>
 */
class FaviconGeneratorResponse
{
    /**
     * The url with all favicons.
     *
     * @var string
     */
    private $packageUrl;

    /**
     * All urls to the files generated.
     *
     * @var array
     */
    private $filesUrl;

    /**
     * True If the user chose to compress the pictures, otherwise false.
     *
     * @var boolean
     */
    private $compression;

    /**
     * @var string
     */
    private $htmlCode;

    /**
     * @var boolean
     */
    private $filesInRoot;

    /**
     * @var string
     */
    private $filesPath;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $storagePath;

    public function __construct($json)
    {
        if ($json == null) {
            throw new InvalidArgumentException("No response from RealFaviconGenerator");
        }

        $response = json_decode($json, true);

        if ($response == null) {
            throw new InvalidArgumentException("JSON could not be parsed");
        }

        $faviconGenerationResult = $this->getParam($response, 'favicon_generation_result');
        $result = $this->getParam($faviconGenerationResult, 'result');
        $status = $this->getParam($result, 'status');

        if ($status != 'success') {
            $msg = $this->getParam($result, 'error_message', false);
            $msg = $msg != null ? $msg : 'An error occured';
            throw new InvalidArgumentException($msg);
        }

        $favicon = $this->getParam($faviconGenerationResult, 'favicon');

        $this->setPackageUrl($this->getParam($favicon, 'package_url'));
        $this->setFilesUrl($this->getParam($favicon, 'files_urls'));
        $this->setIsCompressed($this->getParam($favicon, 'compression') == 'true');
        $this->setHtmlCode($this->getParam($favicon, 'html_code'));
        $filesLoc = $this->getParam($faviconGenerationResult, 'files_location');
        $this->setIsFilesInRoot($this->getParam($filesLoc, 'type') == 'root');
        $this->setFilesPath($this->isFilesInRoot() ? '/' : $this->getParam($filesLoc, 'path'));
        $this->setVersion($this->getParam($faviconGenerationResult, 'version', false));
    }

    /**
     * Download and extract the files referenced by the response sent back by RealFaviconGenerator.
     *
     * @param  string                   $outputDirectory
     * @throws InvalidArgumentException
     */
    public function downloadAndUnpack($outputDirectory, $directoryName = 'favicon_package')
    {
        if ($this->getPackageUrl() != null) {

            $packagePath = $outputDirectory . DIRECTORY_SEPARATOR . $directoryName . '.zip';

            $this->downloadFile($this->getPackageUrl(), $packagePath);

            $zip = new \ZipArchive();

            $r = $zip->open($packagePath);

            if ($r === true) {

                $extractedPath = $outputDirectory . DIRECTORY_SEPARATOR . $directoryName;

                if (!file_exists($extractedPath)) {
                    mkdir($extractedPath);
                }

                $zip->extractTo($extractedPath);
                $zip->close();

                $this->setStoragePath($extractedPath);

            } else {
                throw new InvalidArgumentException('Cannot open package. Invalid Zip file?!');
            }
        }
    }

    /**
     * @return string
     */
    public function getPackageUrl()
    {
        return $this->packageUrl;
    }

    /**
     * @return boolean
     */
    public function isCompressed()
    {
        return $this->compression;
    }

    /**
     * @return string
     */
    public function getHtmlCode()
    {
        return $this->htmlCode;
    }

    /**
     * @return boolean
     */
    public function isFilesInRoot()
    {
        return $this->filesInRoot;
    }

    /**
     * @return string
     */
    public function getFilesPath()
    {
        return $this->filesPath;
    }

    /**
     * The url of the zip with all the favicons.
     *
     * @param string $packageUrl
     */
    public function setPackageUrl($packageUrl)
    {
        $this->packageUrl = $packageUrl;
    }

    /**
     * If the user choosed to compress the images.
     *
     * @param boolean $compression
     */
    public function setIsCompressed($compression)
    {
        $this->compression = $compression;
    }

    /**
     * The HTML to include the favicons.
     *
     * @param string $htmlCode
     */
    public function setHtmlCode($htmlCode)
    {
        $this->htmlCode = $htmlCode;
    }

    /**
     * If the files are to put on the root.
     *
     * @param boolean $filesInRoot
     */
    public function setIsFilesInRoot($filesInRoot)
    {
        $this->filesInRoot = $filesInRoot;
    }

    /**
     * The path to the files.
     *
     * @param string $filesPath
     */
    public function setFilesPath($filesPath)
    {
        $this->filesPath = $filesPath;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function getFilesUrl()
    {
        return $this->filesUrl;
    }

    /**
     * @param array $filesUrl
     */
    public function setFilesUrl($filesUrl)
    {
        $this->filesUrl = $filesUrl;
    }

    /**
     * @return string
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * @param string $storagePath
     */
    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * Returns the value of a parameter.
     *
     * @param  array                    $params
     * @param  string                   $paramName
     * @param  boolean                  $throwIfNotFound
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function getParam($params, $paramName, $throwIfNotFound = true)
    {
        if (isset($params[$paramName])) {
            return $params[$paramName];
        } elseif ($throwIfNotFound) {
            throw new InvalidArgumentException("Cannot find parameter " . $paramName);
        }
    }

    /**
     * Downloads a File from a URL to a local path.
     *
     * @param  string                   $url
     * @param  string                   $localPath
     * @throws InvalidArgumentException
     */
    private function downloadFile($url, $localPath)
    {
        $content = file_get_contents($url);
        if ($content === false) {
            throw new InvalidArgumentException("Cannot download file at " . $url);
        }
        $ret = file_put_contents($localPath, $content);
        if ($ret === false) {
            throw new InvalidArgumentException("Cannot store content of " . $url . " to " . $localPath);
        }
    }

}
