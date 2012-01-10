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

## Configuration

Configure your service definitions via XML, YML or INI files

  * <sf_config_dir>/serviceContainer/services_<env>.<extension>
  * <sf_app_config_dir>/serviceContainer/services_<env>.<extension>
  * <sf_plugin_dir>/config/serviceContainer/services_<env>.<extension>

Example:

    [yml]
    # Configuration for 'prod' environment
    # /home/anton/projects/sf-sandbox/config/serviceContainer/services_prod.yml
    parameters:
      mailer.username: foo
      mailer.password: bar
      mailer.class:    Zend_Mail

    services:
      mail.transport:
        class:     Zend_Mail_Transport_Smtp
        arguments: [smtp.gmail.com, { auth: login, username: %mailer.username%, password: %mailer.password%, ssl: ssl, port: 465 }]
        shared:    false
      mailer:
        class: %mailer.class%
        calls:
          - [setDefaultTransport, [@mail.transport]]

    [yml]
    # Configuration for 'test' environment
    # /home/anton/projects/sf-sandbox/config/serviceContainer/services_test.yml

    # Use prod configuration
    imports:
      - { resource: services_prod.yml }

    # Override mailer service
    services:
      mailer:
        class: MyTest_Mailer_Stub
        calls:
          - [setDefaultTransport, [@mail.transport]]

## Using services in your application

The plugin adds two methods to your ProjectConfiguration and your actions, to
ease the usage.

 * getServiceContainer()
 * getService()

Example:

    [php]
    public function executeIndex(sfWebRequest $request)
    {
      $sc     = $this->getServiceContainer();
      $mailer = $sc->mailer;
      // or
      $mailer = $this->getService('mailer');
      $mailer->sendMail('blah blah blah');
    }