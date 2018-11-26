<?php
namespace Mell\Bundle\SimpleDtoBundle\Services\ORM;

use Doctrine\Common\Persistence\Proxy;

/**
 * Class and reflection related functionality for objects that
 * might or not be proxy objects at the moment.
 * This is a copy-paste of Doctrine's ClassUtils class that was deprecated without new solution provided
 *
 * Helper for working with Doctrine ORM classes
 *
 */
class ClassUtils
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @param string $class
     *
     * @return string
     */
    public function getRealClass($class)
    {
        if (false === $pos = strrpos($class, '\\' . Proxy::MARKER . '\\')) {
            return $class;
        }

        return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * Gets the real class name of an object (even if its a proxy).
     *
     * @param object $object
     *
     * @return string
     */
    public function getClass($object)
    {
        return self::getRealClass(get_class($object));
    }

    /**
     * Gets the real parent class name of a class or object.
     *
     * @param string $className
     *
     * @return string
     */
    public function getParentClass($className)
    {
        return get_parent_class(self::getRealClass($className));
    }
}
