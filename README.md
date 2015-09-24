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

### That was it!
