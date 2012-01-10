<?php

/**
 * sfServiceLocatorPlugin configuration.
 *
 * @package     sfServiceLocatorPlugin
 * @subpackage  config
 * @author      Anton Minin <anton.a.minin@gmail.com>
 */
class sfServiceLocatorPluginConfiguration extends sfPluginConfiguration
{
  protected $serviceContainer;

  /**
   * Initialize the service container
   *
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'initializeServiceContainer'));

    foreach (array('configuration', 'context', 'form', 'response', 'user', 'view') as $component)
      $this->dispatcher->connect("$component.method_not_found", array($this, 'listenToMethodNotFound'));
  }

  /**
   * Method not found event listener
   *
   * Being connected to [component].method_not_found eventExtends symfony
   * components with getServiceContainer and getService
   *
   * @return boolean true to stop notifying other listeners
   * @see sfEventDispatcher::notifyUntil
   */
  public function listenToMethodNotFound(sfEvent $event)
  {
    if ('getServiceContainer' == $event['method'])
    {
      $event->setReturnValue($this->getServiceContainer());
      return true;
    }

    if ('getService' == $event['method'])
    {
      $event->setReturnValue($this->getServiceContainer()->getService($event['arguments'][0]));
      return true;
    }

    return false;
  }

  /**
   * Returns the current service container instance
   *
   * @return sfServiceContainer
   */
  public function getServiceContainer()
  {
    if (!$this->serviceContainer)
      self::initializeServiceContainer(new sfEvent(null, 'service_container.initialize_service_container'));

    return $this->serviceContainer;
  }

  /**
   * Initialize the service container and cache it.
   *
   * Notify a service_container.load_configuration event.
   */
  public function initializeServiceContainer(sfEvent $event)
  {
    $application = sfConfig::get('sf_app');
    $debug       = sfConfig::get('sf_debug');
    $environment = sfConfig::get('sf_environment');
    $name = implode('', array(
      ucfirst($application),
      ucfirst($environment),
      ($debug ? 'Debug' : ''),
      'ServiceContainer'
    ));
    $file = sfConfig::get('sf_app_cache_dir') . '/' . $name.'.php';

    if (!$debug && file_exists($file))
    {
      require_once $file;
      $sc = new $name();
    }
    else
    {
      $sc = self::createServiceContainer($environment);
      $this->dispatcher->notify(new sfEvent($this->serviceContainer, 'service_container.load_configuration'));

      if (!$debug)
      {
        $dumper = new sfServiceContainerDumperPhp($sc);

        file_put_contents($file, $dumper->dump(array('class' => $name)));
      }
    }
    $this->serviceContainer = $sc;
    $this->dispatcher->notify(new sfEvent($this->serviceContainer, 'service_container.post_initialize'));
  }

  /**
   * Create the service container
   *
   * Expected locations of services definition files are
   *  - %sf_config_dir%/serviceContainer/services_%environment%.[yml|xml]
   *  - %sf_app_config_dir%/serviceContainer/services_%environment%.[yml|xml]
   *
   * @see http://components.symfony-project.org/dependency-injection/trunk/book/C-YAML-Format
   * @static
   * @param string  $environment
   * @return sfServiceContainerBuilder
   */
  public static function createServiceContainer($environment)
  {
    $sc = new sfServiceContainerBuilder();

    $loaders = array(
      'yml' => new sfServiceContainerLoaderFileYaml($sc),
      'xml' => new sfServiceContainerLoaderFileXml($sc),
      'ini' => new sfServiceContainerLoaderFileIni($sc)
    );

    foreach (self::getConfigDirs() as $dir)
    {
      foreach ($loaders as $extension => $loader)
      {
        $servicesDefinitionFile = sprintf(
          '%s/serviceContainer/services_%s.%s',
          $dir,
          $environment,
          $extension
        );

        // Don't disturb the user if file doesn't exist
        if (file_exists($servicesDefinitionFile) && is_readable($servicesDefinitionFile))
        {
          $loader->load($servicesDefinitionFile);
        }
      }
    }

    return $sc;
  }

  public static function getConfigDirs()
  {
    $configDirs = array(
      sfConfig::get('sf_config_dir'),
      sfConfig::get('sf_app_config_dir'),
    );

    try {
      $directoryIterator = new DirectoryIterator(sfConfig::get('sf_plugins_dir'));

      foreach ($directoryIterator as $dir) {
        /** @var SplFileInfo $dir */
        if ($dir->isDir() && !preg_match("~^\.~", $dir->getFilename())) {
          $configDirs[] = $dir->getPathname() . '/config';
        }
      }
    } catch (UnexpectedValueException $e) {
      // Silently skip plugin configuration if sf_plugins_dir doesn't exist
    }

    return $configDirs;
  }
}