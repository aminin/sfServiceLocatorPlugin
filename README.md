# sfServiceLocatorPlugin

The `sfServiceLocatorPlugin` wraps the [dependency injection component for
Symfony1](http://components.symfony-project.org/dependency-injection/)


## Installation

  * Install the plugin

        $ git submodule add https://github.com/aminin/sfServiceLocatorPlugin.git
        $ git submodule update --init --recursive

  * Clear the cache

        $ symfony cc

  * Activate the plugin in the `config/ProjectConfiguration.class.php`:

        [php]
        class ProjectConfiguration extends sfProjectConfiguration
        {
          public function setup()
          {
            $this->enablePlugins(array(
              /* ... */
              'sfServiceLocatorPlugin',
            ));
          }
        }

## Documentation

### Use services in your application

The plugin adds two methods to your ProjectConfiguration and your actions, to
ease the usage.

 * getServiceContainer()
 * getService()

Example:

    [php]
    public function executeIndex(sfWebRequest $request)
    {
      $sc   = $this->getServiceContainer();
      $mail = $sc->mail;
      // or
      $mail = $this->getService('mail');
    }