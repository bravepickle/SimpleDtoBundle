<?php

namespace Mell\Bundle\SimpleDtoBundle\CacheWarmer;

use Doctrine\Common\Annotations\AnnotationException;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Factory\ClassMetadataFactory;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

/**
 * Warms up XML and YAML serializer metadata.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SerializerCacheWarmer implements CacheWarmerInterface
{
    private $loaders;
    private $phpArrayFile;

    /**
     * @param LoaderInterface[]      $loaders      The serializer metadata loaders
     * @param string                 $phpArrayFile The PHP file where metadata are cached
     */
    public function __construct(array $loaders, $phpArrayFile)
    {
        if (2 < \func_num_args() && func_get_arg(2) instanceof CacheItemPoolInterface) {
            @trigger_error(sprintf('The CacheItemPoolInterface $fallbackPool argument of "%s()" is deprecated since Symfony 4.2, you should not pass it anymore.', __METHOD__), E_USER_DEPRECATED);
        }

        $this->loaders = $loaders;
        $this->phpArrayFile = $phpArrayFile;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        if (!class_exists(CacheClassMetadataFactory::class)
            || !method_exists(XmlFileLoader::class, 'getMappedClasses')
            || !method_exists(YamlFileLoader::class, 'getMappedClasses')
        ) {
            return;
        }

        $adapter = new PhpArrayAdapter($this->phpArrayFile, new NullAdapter());
        $arrayPool = new ArrayAdapter(0, false);

        $metadataFactory = new CacheClassMetadataFactory(
            new ClassMetadataFactory(new LoaderChain($this->loaders)), $arrayPool
        );

        spl_autoload_register(array($adapter, 'throwOnRequiredClass'));
        try {
            foreach ($this->extractSupportedLoaders($this->loaders) as $loader) {
                foreach ($loader->getMappedClasses() as $mappedClass) {
                    try {
                        $metadataFactory->getMetadataFor($mappedClass);
                    } catch (\ReflectionException $e) {
                        // ignore failing reflection
                    } catch (AnnotationException $e) {
                        // ignore failing annotations
                    }
                }
            }
        } finally {
            spl_autoload_unregister(array($adapter, 'throwOnRequiredClass'));
        }

        $values = $arrayPool->getValues();
        $adapter->warmUp($values);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * @param LoaderInterface[] $loaders
     *
     * @return XmlFileLoader[]|YamlFileLoader[]
     */
    private function extractSupportedLoaders(array $loaders)
    {
        $supportedLoaders = array();

        foreach ($loaders as $loader) {
            if ($loader instanceof XmlFileLoader || $loader instanceof YamlFileLoader) {
                $supportedLoaders[] = $loader;
            } elseif ($loader instanceof LoaderChain) {
                $supportedLoaders = array_merge(
                    $supportedLoaders,
                    $this->extractSupportedLoaders($loader->getDelegatedLoaders())
                );
            }
        }

        return $supportedLoaders;
    }
}
