# RealFaviconGeneratorBundle

> Integrate generation of a multiplatform favicon with [RealFaviconGenerator](http://realfavicongenerator.net/) into your Symfony application.


## Installation

### Get the bundle using composer

Add RealFaviconGeneratorBundle by running this command from the terminal at the root of
your Symfony project:

```bash
composer require venture-oak-labs/favicon-generator-bundle
```


### Enable the bundle

To start using the bundle, register the bundle in your application's kernel class:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new VentureOakLabs\FaviconGeneratorBundle\VentureOakLabsFaviconGeneratorBundle(),
        // ...
    );
}
```

### Configure bundle

```yaml
# app/config/config.yml
venture_oak_labs_favicon_generator:
    api_key: #required
```

### How to use
```php
// The bundle provides a service to generate the favicons package based on a set of options.
$generator = $this->container->get('venture_oak_labs.favicon_generator.generator');

// Options
$options = array(
    'general' => array(
        'src' => # Required ( URL For image or image content encoded in Base64),
        'icons_path' => # Defaults to 'root'
    ),
    'design' => array( 
        'desktop_browser', # By default provides Design for the classic desktop browsers. Configure the other as you like!
        'ios',
        'windows',
        'firefox_app',
        'android_chrome',
        'coast',
        'yandex_browser'
    ),
    'settings' => array(
        'compression',
        'scaling_algorithm',
        'error_on_image_too_small'
    )
);

$response = $generator->generate($options)

// The generated files have an available limit time. You can download and unpack them.
$response->downloadAndUnpack($outputDirectory, $directoryName) # Directory Name defaults to 'favicon_package'

```

Further reading about the available options: http://realfavicongenerator.net/api/non_interactive_api#.VgQ5Frw2tZ4


### This bundle is still in beta version!
