<?php

namespace Application\Mapper;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Initializer implements InitializerInterface
{

    /**
     * Initialize
     *
     * @param mixed                   $instance
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MapperDoctrineInterface
     * @throws \Exception
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof MapperDoctrineInterface) {

            $entityClassName = str_replace('Mapper', 'Entity', get_class($instance));

            if (class_exists($entityClassName)) {
                $instance->setEntityClassName($entityClassName);
            } else {
                throw new \Exception('Entity ' . $entityClassName . " class doesn't exist");
            }
        }

        return $instance;
    }

}