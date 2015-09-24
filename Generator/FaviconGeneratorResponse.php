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
    const RFG_PACKAGE_URL = 'package_url';
    const RFG_COMPRESSION = 'compression';
    const RFG_HTML_CODE = 'html_code';
    const RFG_FILES_IN_ROOT = 'files_in_root';
    const RFG_FILES_PATH = 'files_path';
    const RFG_PREVIEW_PICTURE_URL = 'preview_picture_url';
    const RFG_CUSTOM_PARAMETER  = 'custom_parameter';
    const RFG_VERSION = 'version';
    const RFG_NON_INTERACTIVE_REQUEST = 'non_interactive_request';
    const RFG_FAVICON_PRODUCTION_PACKAGE_PATH = 'favicon_production_package_path';
    const RFG_PREVIEW_PATH = 'preview_path';

    private $params = array();

    public function __construct($json)
    {
        if ($json == NULL) {
            throw new InvalidArgumentException("No response from RealFaviconGenerator");
        }

        $response = json_decode($json, true);

        if ($response == NULL) {
            throw new InvalidArgumentException("JSON could not be parsed");
        }

        $response = $this->getParam($response, 'favicon_generation_result');
        $result = $this->getParam($response, 'result');
        $status = $this->getParam($result, 'status');

        if ($status != 'success') {
            $msg = $this->getParam($result, 'error_message', false);
            $msg = $msg != NULL ? $msg : 'An error occured';
            throw new InvalidArgumentException($msg);
        }

        $favicon = $this->getParam($response, 'favicon');
        $this->params[self::RFG_PACKAGE_URL] = $this->getParam($favicon, 'package_url');
        $this->params[self::RFG_COMPRESSION] = $this->getParam($favicon, 'compression') == 'true';
        $this->params[self::RFG_HTML_CODE] = $this->getParam($favicon, 'html_code');

        $filesLoc = $this->getParam($response, 'files_location');
        $this->params[self::RFG_FILES_IN_ROOT] = $this->getParam($filesLoc, 'type') == 'root';
        $this->params[self::RFG_FILES_PATH] = $this->params[self::RFG_FILES_IN_ROOT] ? '/' : $this->getParam($filesLoc, 'path');

        $this->params[self::RFG_PREVIEW_PICTURE_URL] = $this->getParam($response, 'preview_picture_url', false);

        $this->params[self::RFG_CUSTOM_PARAMETER] = $this->getParam($response, 'custom_parameter', false);
        $this->params[self::RFG_VERSION] = $this->getParam($response, 'version', false);

        $this->params[self::RFG_NON_INTERACTIVE_REQUEST] = $this->getParam($response, 'non_interactive_request', false);
    }

    /**
     * For example: <code>"http://realfavicongenerator.net/files/1234f5d2s34f3ds2/package.zip"</code>
     */
    public function getPackageUrl()
    {
        return $this->params[self::RFG_PACKAGE_URL];
    }

    /**
     * For example: <code>"&lt;link ..."</code>
     */
    public function getHtmlCode()
    {
        return $this->params[self::RFG_HTML_CODE];
    }

    /**
     * <code>true</code> if the user chose to compress the pictures, <code>false</code> otherwise.
     */
    public function isCompressed()
    {
        return $this->params[self::RFG_COMPRESSION];
    }

    /**
     * <code>true</code> if the favicon files are to be stored in the root directory of the target web site, <code>false</code> otherwise.
     */
    public function isFilesInRoot()
    {
        return $this->params[self::RFG_FILES_IN_ROOT];
    }

    /**
     * Indicate where the favicon files should be stored in the target web site. For example: <code>"/"</code>, <code>"/path/to/icons"</code>.
     */
    public function getFilesLocation()
    {
        return $this->params[self::RFG_FILES_PATH];
    }

    /**
     * For example: <code>"http://realfavicongenerator.net/files/1234f5d2s34f3ds2/preview.png"</code>
     */
    public function getPreviewUrl()
    {
        return $this->params[self::RFG_PREVIEW_PICTURE_URL];
    }

    /**
     * Return the customer parameter, as it was transmitted during the invocation of the API.
     */
    public function getCustomParameter()
    {
        return $this->params[self::RFG_CUSTOM_PARAMETER];
    }

    /**
     * Return version of RealFaviconGenerator used to generate the favicon. Save this value to later check for updates.
     */
    public function getVersion()
    {
        return $this->params[self::RFG_VERSION];
    }

    /**
     * Directory where the production favicon files are stored.
     * These are the files to deployed to the targeted web site.
     * Method <code>downloadAndUnpack</code> must be called first in order to populate this field.
     */
    public function getPackagePath()
    {
        return $this->params[self::RFG_FAVICON_PRODUCTION_PACKAGE_PATH];
    }

    /**
     * Path to the preview picture.
     */
    public function getPreviewPath()
    {
        return $this->params[self::RFG_PREVIEW_PATH];
    }

    /**
     * Non-interative request.
     */
    public function getNonInteractiveRequest()
    {
        return $this->params[self::RFG_NON_INTERACTIVE_REQUEST];
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

}
