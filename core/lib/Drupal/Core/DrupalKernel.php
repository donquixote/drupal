<?php

/**
 * @file
 * Definition of Drupal\Core\DrupalKernel.
 */

namespace Drupal\Core;

use Drupal\Component\PhpStorage\PhpStorageFactory;
use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\CoreServiceProvider;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\YamlFileLoader;
use Drupal\Core\Extension\ModuleHandlerFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Composer\Autoload\ClassLoader;

/**
 * The DrupalKernel class is the core of Drupal itself.
 *
 * This class is responsible for building the Dependency Injection Container and
 * also deals with the registration of service providers. It allows registered
 * service providers to add their services to the container. Core provides the
 * CoreServiceProvider, which, in addition to registering any core services that
 * cannot be registered in the core.services.yaml file, adds any compiler passes
 * needed by core, e.g. for processing tagged services. Each module can add its
 * own service provider, i.e. a class implementing
 * Drupal\Core\DependencyInjection\ServiceProvider, to register services to the
 * container, or modify existing services.
 */
class DrupalKernel implements DrupalKernelInterface, TerminableInterface {

  const CONTAINER_BASE_CLASS = '\Drupal\Core\DependencyInjection\Container';

  /**
   * Holds the container instance.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The environment, e.g. 'testing', 'install'.
   *
   * @var string
   */
  protected $environment;

  /**
   * Whether the kernel has been booted.
   *
   * @var bool
   */
  protected $booted;

  /**
   * Holds an updated list of enabled modules.
   *
   * @var array
   *   An associative array whose keys are module names and whose values are
   *   ignored.
   */
  protected $newModuleList;

  /**
   * An array of module data objects.
   *
   * The data objects have the same data structure as returned by
   * file_scan_directory() but only the uri property is used.
   *
   * @var array
   */
  protected $moduleData = array();

  /**
   * ModuleHandler to get information on installed modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * PHP code storage object to use for the compiled container.
   *
   * @var \Drupal\Component\PhpStorage\PhpStorageInterface
   */
  protected $storage;

  /**
   * The classloader object.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  protected $classLoader;

  /**
   * Config storage object used for reading enabled modules configuration.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * The list of the classnames of the service providers in this kernel.
   *
   * @var array
   */
  protected $serviceProviderClasses;

  /**
   * Whether the container can be dumped.
   *
   * @var bool
   */
  protected $allowDumping;

  /**
   * Whether the container needs to be dumped once booting is complete.
   *
   * @var bool
   */
  protected $containerNeedsDumping;

  /**
   * Holds the list of YAML files containing service definitions.
   *
   * @var array
   */
  protected $serviceYamls;

  /**
   * The array of registered service providers.
   *
   * @var array
   */
  protected $serviceProviders;

  /**
   * Constructs a DrupalKernel object.
   *
   * @param string $environment
   *   String indicating the environment, e.g. 'prod' or 'dev'. Used by
   *   Symfony\Component\HttpKernel\Kernel::__construct(). Drupal does not use
   *   this value currently. Pass 'prod'.
   * @param \Composer\Autoload\ClassLoader $class_loader
   *   (optional) The classloader is only used if $storage is not given or
   *   the load from storage fails and a container rebuild is required. In
   *   this case, the loaded modules will be registered with this loader in
   *   order to be able to find the module serviceProviders.
   * @param bool $allow_dumping
   *   (optional) FALSE to stop the container from being written to or read
   *   from disk. Defaults to TRUE.
   */
  public function __construct($environment, ClassLoader $class_loader, $allow_dumping = TRUE) {
    $this->environment = $environment;
    $this->booted = false;
    $this->classLoader = $class_loader;
    $this->allowDumping = $allow_dumping;
  }

  /**
   * {@inheritdoc}
   */
  public function boot() {
    if ($this->booted) {
      return;
    }

    $this->configStorage = BootstrapConfigStorageFactory::get();
    $this->moduleHandler = ModuleHandlerFactory::get($this->configStorage);
    $this->initializeContainer();
    $this->booted = TRUE;
    if ($this->containerNeedsDumping && !$this->dumpDrupalContainer($this->container, static::CONTAINER_BASE_CLASS)) {
      watchdog('DrupalKernel', 'Container cannot be written to disk');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function shutdown() {
    if (FALSE === $this->booted) {
      return;
    }
    $this->booted = FALSE;
    $this->container = null;
  }

  /**
   * {@inheritdoc}
   */
  public function getContainer() {
    return $this->container;
  }

  /**
   * {@inheritdoc}
   */
  public function discoverServiceProviders() {
    $serviceProviders = array(
      'CoreServiceProvider' => new CoreServiceProvider(),
    );
    $this->serviceYamls = array(
      'core/core.services.yml'
    );
    $this->serviceProviderClasses = array('Drupal\Core\CoreServiceProvider');

    $this->registerNamespaces($this->moduleHandler->getModuleNamespaces());

    // Load each module's serviceProvider class.
    foreach ($this->moduleHandler->getModuleNames() as $module) {
      $camelized = ContainerBuilder::camelize($module);
      $name = "{$camelized}ServiceProvider";
      $class = "Drupal\\{$module}\\{$name}";
      if (class_exists($class)) {
        $serviceProviders[$name] = new $class();
        $this->serviceProviderClasses[] = $class;
      }
      $filename = $this->moduleHandler->getModuleDirectory($module) . "/$module.services.yml";
      if (file_exists($filename)) {
        $this->serviceYamls[] = $filename;
      }
    }

    // Add site specific or test service providers.
    if (!empty($GLOBALS['conf']['container_service_providers'])) {
      foreach ($GLOBALS['conf']['container_service_providers'] as $name => $class) {
        $serviceProviders[$name] = new $class();
        $this->serviceProviderClasses[] = $class;
      }
    }
    // Add site specific or test YAMLs.
    if (!empty($GLOBALS['conf']['container_yamls'])) {
      $this->serviceYamls = array_merge($this->serviceYamls, $GLOBALS['conf']['container_yamls']);
    }
    return $serviceProviders;
  }


  /**
   * {@inheritdoc}
   */
  public function getServiceProviders() {
    return $this->serviceProviders;
  }

  /**
   * {@inheritdoc}
   */
  public function terminate(Request $request, Response $response) {
    if (FALSE === $this->booted) {
      return;
    }

    if ($this->getHttpKernel() instanceof TerminableInterface) {
      $this->getHttpKernel()->terminate($request, $response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
    if (FALSE === $this->booted) {
      $this->boot();
    }

    return $this->getHttpKernel()->handle($request, $type, $catch);
  }

  /**
   * Rebuilds the container during the request. Used when the list of modules
   * changes.
   */
  public function rebuildContainer() {
    // If we haven't yet booted, we don't need to do anything. If we have
    // already booted, then reboot in order to refresh the bundle list and
    // container.
    if ($this->booted) {
      $this->moduleHandler = ModuleHandlerFactory::get($this->configStorage);
      $this->initializeContainer(TRUE);
    }
  }

  /**
   * Returns the classname based on environment and testing prefix.
   *
   * @return string
   *   The class name.
   */
  protected function getClassName() {
    $parts = array('service_container', $this->environment);
    // Make sure to use a testing-specific container even in the parent site.
    if (!empty($GLOBALS['drupal_test_info']['test_run_id'])) {
      $parts[] = $GLOBALS['drupal_test_info']['test_run_id'];
    }
    elseif ($prefix = drupal_valid_test_ua()) {
      $parts[] = $prefix;
    }
    return implode('_', $parts);
  }


  /**
   * Returns the kernel parameters.
   *
   * @return array An array of kernel parameters
   */
  protected function getKernelParameters() {
    return array(
      'kernel.environment' => $this->environment,
    );
  }

  /**
   * Initializes the service container.
   */
  protected function initializeContainer($rebuild = FALSE) {
    $persist = $this->getServicesToPersist();
    // The request service requires custom persisting logic, since it is also
    // potentially scoped. During Drupal installation, there is a request
    // service without a request scope.
    $request_scope = FALSE;
    if (isset($this->container)) {
      if ($this->container->isScopeActive('request')) {
        $request_scope = TRUE;
      }
      if ($this->container->initialized('request')) {
        $request = $this->container->get('request');
      }
    }
    $this->container = NULL;
    $class = $this->getClassName();

    if ($this->allowDumping) {
      $this->container = $this->initializeCachedContainer($class, $persist);
    }

    if ($rebuild) {
      unset($this->container);
    }

    // Second, check if some other request -- for example on another web
    // frontend or during the installer -- changed the list of enabled modules.
    if (isset($this->container)) {
      // All namespaces must be registered before we attempt to use any service
      // from the container.
      $container_modules = $this->container->getParameter('container.modules');
      $namespaces_before = $this->classLoader->getPrefixes();
      $this->registerNamespaces($this->moduleHandler->getModuleNamespaces($container_modules));

      if ($this->moduleHandler->getModuleNames() !== array_keys($container_modules)) {
        $persist = $this->getServicesToPersist();
        unset($this->container);
        // Revert the class loader to its prior state. However,
        // registerNamespaces() performs a merge rather than replace, so to
        // effectively remove erroneous registrations, we must replace them with
        // empty arrays.
        $namespaces_after = $this->classLoader->getPrefixes();
        $namespaces_before += array_fill_keys(array_diff(array_keys($namespaces_after), array_keys($namespaces_before)), array());
        $this->registerNamespaces($namespaces_before);
      }
    }

    if (!isset($this->container)) {
      $this->container = $this->buildContainer();
      $this->persistServices($this->container, $persist);

      // The namespaces are marked as persistent, so objects like the annotated
      // class discovery still has the right object. We may have updated the
      // list of modules, so set it.
      if ($this->container->initialized('container.namespaces')) {
        $this->container->get('container.namespaces')->exchangeArray($this->container->getParameter('container.namespaces'));
      }

      if ($this->allowDumping) {
        $this->containerNeedsDumping = TRUE;
      }
    }

    $this->container->set('kernel', $this);

    // Set the class loader which was registered as a synthetic service.
    $this->container->set('class_loader', $this->classLoader);
    // If we have a request set it back to the new container.
    if ($request_scope) {
      $this->container->enterScope('request');
    }
    if (isset($request)) {
      $this->container->set('request', $request);
    }

    $this->container->get('event_dispatcher')->addListener(
      'drupal.update_modules', array($this, 'rebuildContainer')
    );

    \Drupal::setContainer($this->container);
  }

  /**
   * Returns service instances to persist from an old container to a new one.
   */
  protected function getServicesToPersist() {
    $persist = array();
    if (isset($this->container)) {
      foreach ($this->container->getParameter('persistIds') as $id) {
        // It's pointless to persist services not yet initialized.
        if ($this->container->initialized($id)) {
          $persist[$id] = $this->container->get($id);
        }
      }
    }
    return $persist;
  }

  /**
   * Moves persistent service instances into a new container.
   */
  protected function persistServices($container, array $persist) {
    foreach ($persist as $id => $object) {
      // Do not override services already set() on the new container, for
      // example 'service_container'.
      if (!$container->initialized($id)) {
        $container->set($id, $object);
      }
    }
  }

  /**
   * Builds the service container.
   *
   * @return ContainerBuilder The compiled service container
   */
  protected function buildContainer() {
    $this->initializeServiceProviders();
    $container = $this->getContainerBuilder();
    $container->set('kernel', $this);
    $container->setParameter('container.service_providers', $this->serviceProviderClasses);
    $container->setParameter('container.modules', $this->moduleHandler->getModuleList());

    // Get a list of namespaces and put it onto the container.
    $namespaces = $this->moduleHandler->getModuleNamespaces();
    // Add all components in \Drupal\Core and \Drupal\Component that have a
    // Plugin directory.
    foreach (array('Core', 'Component') as $parent_directory) {
      $path = DRUPAL_ROOT . '/core/lib/Drupal/' . $parent_directory;
      foreach (new \DirectoryIterator($path) as $component) {
        if (!$component->isDot() && is_dir($component->getPathname() . '/Plugin')) {
          $namespaces['Drupal\\' . $parent_directory  .'\\' . $component->getFilename()] = DRUPAL_ROOT . '/core/lib';
        }
      }
    }
    $container->setParameter('container.namespaces', $namespaces);

    // Register synthetic services.
    $container->register('class_loader')->setSynthetic(TRUE);
    $container->register('kernel', 'Symfony\Component\HttpKernel\KernelInterface')->setSynthetic(TRUE);
    $container->register('service_container', 'Symfony\Component\DependencyInjection\ContainerInterface')->setSynthetic(TRUE);
    $yaml_loader = new YamlFileLoader($container);
    foreach ($this->serviceYamls as $filename) {
      $yaml_loader->load($filename);
    }
    foreach ($this->serviceProviders as $provider) {
      if ($provider instanceof ServiceProviderInterface) {
        $provider->register($container);
      }
    }

    // Identify all services whose instances should be persisted when rebuilding
    // the container during the lifetime of the kernel (e.g., during a kernel
    // reboot). Include synthetic services, because by definition, they cannot
    // be automatically reinstantiated. Also include services tagged to persist.
    $persist_ids = array();
    foreach ($container->getDefinitions() as $id => $definition) {
      if ($definition->isSynthetic() || $definition->getTag('persist')) {
        $persist_ids[] = $id;
      }
    }
    $container->setParameter('persistIds', $persist_ids);

    $container->compile();
    return $container;
  }

  /**
   * Registers all service providers to the kernel.
   *
   * @throws \LogicException
   */
  protected function initializeServiceProviders() {
    $this->serviceProviders = array();

    foreach ($this->discoverServiceProviders() as $name => $provider) {
      if (isset($this->serviceProviders[$name])) {
        throw new \LogicException(sprintf('Trying to register two service providers with the same name "%s"', $name));
      }
      $this->serviceProviders[$name] = $provider;
    }
  }

  /**
   * Gets a new ContainerBuilder instance used to build the service container.
   *
   * @return ContainerBuilder
   */
  protected function getContainerBuilder() {
    return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
  }

  /**
   * Dumps the service container to PHP code in the config directory.
   *
   * This method is based on the dumpContainer method in the parent class, but
   * that method is reliant on the Config component which we do not use here.
   *
   * @param ContainerBuilder $container
   *   The service container.
   * @param string $baseClass
   *   The name of the container's base class
   *
   * @return bool
   *   TRUE if the container was successfully dumped to disk.
   */
  protected function dumpDrupalContainer(ContainerBuilder $container, $baseClass) {
    if (!$this->storage()->writeable()) {
      return FALSE;
    }
    // Cache the container.
    $dumper = new PhpDumper($container);
    $class = $this->getClassName();
    $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));
    return $this->storage()->save($class . '.php', $content);
  }


  /**
   * Gets a http kernel from the container
   *
   * @return HttpKernel
   */
  protected function getHttpKernel() {
    return $this->container->get('http_kernel');
  }

  /**
   * Overrides and eliminates this method from the parent class. Do not use.
   *
   * This method is part of the KernelInterface interface, but takes an object
   * implementing LoaderInterface as its only parameter. This is part of the
   * Config compoment from Symfony, which is not provided by Drupal core.
   *
   * Modules wishing to provide an extension to this class which uses this
   * method are responsible for ensuring the Config component exists.
   */
  public function registerContainerConfiguration(LoaderInterface $loader) {
  }

  /**
   * Gets the PHP code storage object to use for the compiled container.
   *
   * @return \Drupal\Component\PhpStorage\PhpStorageInterface
   */
  protected function storage() {
    if (!isset($this->storage)) {
      $this->storage = PhpStorageFactory::get('service_container');
    }
    return $this->storage;
  }

  /**
   * Registers a list of namespaces.
   *
   * @param array $namespaces
   *   An array of namespaces.to register.
   */
  protected function registerNamespaces(array $namespaces = array()) {
    foreach ($namespaces as $prefix => $path) {
      $this->classLoader->add($prefix, $path);
    }
  }

  /**
   * Initializes the cached container.
   *
   * @param string $class
   *   The classname of the cached container.
   * @param bool $persist
   *   Whether or not to persist the cached container.
   * @return ContainerInterface
   *   The cached container.
   */
  protected function initializeCachedContainer($class, $persist) {
    $container = NULL;
    $cache_file = $class . '.php';
    // First, try to load.
    if (!class_exists($class, FALSE)) {
      $this->storage()->load($cache_file);
    }
    // If the load succeeded or the class already existed, use it.
    if (class_exists($class, FALSE)) {
      $fully_qualified_class_name = '\\' . $class;
      $container = new $fully_qualified_class_name;
      $this->persistServices($container, $persist);
    }
    return $container;
  }
}
