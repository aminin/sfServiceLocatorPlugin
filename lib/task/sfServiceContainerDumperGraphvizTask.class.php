<?php
/**
 * sfServiceContainerDumperGraphvizTask.
 *
 * @package     sfServiceContainerDumperGraphvizTask
 * @subpackage  task
 * @author      Anton Minin <anton.a.minin@gmail.com>
 */
class sfServiceContainerDumperGraphvizTask extends sfBaseTask
{
  protected function configure()
  {
    // the default symfony task options
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'services';
    $this->name             = 'dump-graphviz';
    $this->briefDescription = 'generates a \'dot\' representation of your service container';
    $this->detailedDescription = <<<EOF
The [services:dump-graphiz|INFO] task generates a dot representation of your service container
Call it with:

  [php symfony services:dump-graphiz|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $sc = $this->configuration->getServiceContainer();
    $dumper = new sfServiceContainerDumperGraphviz($sc);
    file_put_contents(sfConfig::get('sf_data_dir').'/container.dot', $dumper->dump());
  }
}
 
