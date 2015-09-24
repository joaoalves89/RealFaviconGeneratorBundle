<?php

namespace VentureOakLabs\FaviconGeneratorBundle\Generator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Favicon Generator.
 *
 * @author João Alves <jalves@ventureoak.com>
 */
class FaviconGenerator
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     *
     * The endpoint of the real favicon generator non interact API.
     */
    protected $apiEndpoint = 'http://realfavicongenerator.net/api/favicon';

    /**
     * Favicon Generator Options.
     *
     * @var array
     */
    protected $options;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Calls the API to generate a favicon based on a set of configurations.
     *
     * @param  array                    $options
     * @return FaviconGeneratorResponse
     */
    public function generateFavicon($options = array())
    {
        $this->parseOptions($options);

        $data = $this->buildRequestData();

        $dataStr = json_encode($data, JSON_FORCE_OBJECT);

        $ch = curl_init($this->apiEndpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);

        $output = curl_exec($ch);

        curl_close($ch);

        return new FaviconGeneratorResponse($output);

    }

    /**
     * Configure Settings.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    private function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'compression',
            'scaling_algorithm',
            'error_on_image_too_small'
        ));

        $resolver->setDefaults(array(
            'error_on_image_too_small' => false
        ));

        $resolver->setAllowedTypes('compression', 'int');
        $resolver->setAllowedTypes('scaling_algorithm', 'string');
        $resolver->setAllowedTypes('error_on_image_too_small', 'boolean');

        $resolver->setAllowedValues('compression', array(0, 1, 2, 3, 4, 5));
        $resolver->setAllowedValues('scaling_algorithm', array('Mitchell', 'NearestNeighbor', 'Cubic', 'Bilinear', 'Lanczos', 'Spline'));

    }

    /**
     * Configure the general options.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    private function configureGeneral(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'src', // The icon master picture. It must be a square, high definition picture. This picture can be passed as a file or a URL,
        ]);

        $resolver->setAllowedTypes('src', 'string');

        $resolver->setAllowedValues('src', function ($value) {
            // Cannot be null
            if (trim($value) === '') {
                return false;
            } else {
                return true;
            }
        });

        // This is where the icons will be stored and accessible. This path is about the target web site, not the local file system.
        $resolver->setDefaults(['icons_path' => null]);
        $resolver->setAllowedTypes('icons_path', ['null', 'string']);

    }

    /**
     * Configure favicon design.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    private function configureDesign(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'desktop_browser', // Design for the classic desktop browsers. This section has no parameters. The philosophy behind is that the master picture is usually designed for this purpose
            'ios',
            'windows',
            'firefox_app',
            'android_chrome',
            'coast',
            'yandex_browser'
        ]);

        $resolver->setAllowedTypes('ios', 'array');
        $resolver->setAllowedTypes('windows', 'array');
        $resolver->setAllowedTypes('firefox_app', 'array');
        $resolver->setAllowedTypes('android_chrome', 'array');
        $resolver->setAllowedTypes('coast', 'array');
        $resolver->setAllowedTypes('yandex_browser', 'array');

    }

    /**
     * Configure all expected options.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'general',
        ]);
        $resolver->setAllowedTypes('general', 'array');

        $resolver->setDefaults([
            'settings' => [],
            'design'   => [],
        ]);

        $resolver->setAllowedTypes('settings', 'array');
        $resolver->setAllowedTypes('design', 'array');

    }

    /**
     * Parses the Options provided to generate a favicon.
     */
    private function parseOptions($options = array())
    {
        // Parse all Options
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        // Parse General Options
        $generalResolver = new OptionsResolver();
        $this->configureGeneral($generalResolver);
        $this->options['general'] = $generalResolver->resolve($this->options['general']);

        // Parse Settings Options
        $settingsResolver = new OptionsResolver();
        $this->configureSettings($settingsResolver);
        $this->options['settings'] = $settingsResolver->resolve($this->options['settings']);

        // Parse Design Options
        $designResolver = new OptionsResolver();
        $this->configureDesign($designResolver);
        $this->options['design'] = $designResolver->resolve($this->options['design']);

    }

    /**
     * Builds the data to be passed to the API.
     *
     * @return array
     */
    private function buildRequestData()
    {
        $data = array(
            'api_key' => $this->apiKey,
            'master_picture' => array(),
            'files_location' => array()
        );

        if ($this->isUrl($this->options['general']['src'])) {
            $data['master_picture']['type'] = 'url';
            $data['master_picture']['url'] = $this->options['general']['src'];
        } else {
            $data['master_picture']['type'] = 'inline';
            $data['master_picture']['content'] = $this->options['general']['src'];
        }

        if (!$this->options['general']['icons_path']) {
            $data['files_location']['type'] = 'root';
        } else {
            $data['files_location']['type'] = 'path';
            $data['files_location']['type'] = $this->options['general']['icons_path'];
        }

        if (!empty($this->options['design'])) {
            $data['favicon_design'] = $this->options['design'];
        }

        if (!empty($this->options['settings'])) {
            $data['settings'] = $this->options['settings'];
        }

        return array('favicon_generation' => $data);

    }

    /**
     * Checks if a string string starts with a value.
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @return boolean
     */
    private function startsWith($haystack, $needle)
    {
         $length = strlen($needle);

         return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Checks if a value is a url.
     *
     * @param string $urlOrPath
     */
    private function isUrl($urlOrPath)
    {
        return $this->startsWith($urlOrPath, 'http://') || $this->startsWith($urlOrPath, 'https://') || $this->startsWith($urlOrPath, '//');

    }

}
