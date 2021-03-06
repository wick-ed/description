<?php

/**
 * AppserverIo\Description\DescriptorReferencesTrait
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/description
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Description;

use AppserverIo\Lang\Reflection\ClassInterface;
use AppserverIo\Psr\EnterpriseBeans\Description\EpbReferenceDescriptorInterface;
use AppserverIo\Psr\EnterpriseBeans\Description\ResReferenceDescriptorInterface;
use AppserverIo\Psr\EnterpriseBeans\Description\PersistenceUnitReferenceDescriptorInterface;

/**
 * Trait with functionality to handle bean, resource and persistence unit references.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/description
 * @link      http://www.appserver.io
 */
trait DescriptorReferencesTrait
{

    /**
     * The array with the EPB references.
     *
     * @var array
     */
    protected $epbReferences = array();

    /**
     * The array with the resource references.
     *
     * @var array
     */
    protected $resReferences = array();

    /**
     * The array with the persistence unit references.
     *
     * @var array
     */
    protected $persistenceUnitReferences = array();

    /**
     * Adds a EPB reference configuration.
     *
     * @param \AppserverIo\Psr\EnterpriseBeans\Description\EpbReferenceDescriptorInterface $epbReference The EPB reference configuration
     *
     * @return void
     */
    public function addEpbReference(EpbReferenceDescriptorInterface $epbReference)
    {
        $this->epbReferences[$epbReference->getName()] = $epbReference;
    }

    /**
     * Sets the array with the EPB references.
     *
     * @param array $epbReferences The EPB references
     *
     * @return void
     */
    public function setEpbReferences(array $epbReferences)
    {
        $this->epbReferences = $epbReferences;
    }

    /**
     * The array with the EPB references.
     *
     * @return array The EPB references
     */
    public function getEpbReferences()
    {
        return $this->epbReferences;
    }

    /**
     * Adds a resource reference configuration.
     *
     * @param \AppserverIo\Psr\EnterpriseBeans\Description\ResReferenceDescriptorInterface $resReference The resource reference configuration
     *
     * @return void
     */
    public function addResReference(ResReferenceDescriptorInterface $resReference)
    {
        $this->resReferences[$resReference->getName()] = $resReference;
    }

    /**
     * Sets the array with the resource references.
     *
     * @param array $resReferences The resource references
     *
     * @return void
     */
    public function setResReferences(array $resReferences)
    {
        $this->resReferences = $resReferences;
    }

    /**
     * The array with the resource references.
     *
     * @return array The resource references
     */
    public function getResReferences()
    {
        return $this->resReferences;
    }

    /**
     * Adds a persistence unit reference configuration.
     *
     * @param \AppserverIo\Psr\EnterpriseBeans\Description\PersistenceUnitReferenceDescriptorInterface $persistenceUnitReference The persistence unit reference configuration
     *
     * @return void
     */
    public function addPersistenceUnitReference(PersistenceUnitReferenceDescriptorInterface $persistenceUnitReference)
    {
        $this->persistenceUnitReferences[$persistenceUnitReference->getName()] = $persistenceUnitReference;
    }

    /**
     * Sets the array with the persistence unit references.
     *
     * @param array $persistenceUnitReferences The persistence unit references
     *
     * @return void
     */
    public function setPersistenceUnitReferences(array $persistenceUnitReferences)
    {
        $this->persistenceUnitReferences = $persistenceUnitReferences;
    }

    /**
     * The array with the persistence unit references.
     *
     * @return array The persistence unit references
     */
    public function getPersistenceUnitReferences()
    {
        return $this->persistenceUnitReferences;
    }

    /**
     * Returns an array with the merge EBP, resource and persistence unit references.
     *
     * @return array The array with the merge all bean references
     */
    public function getReferences()
    {
        return array_merge($this->epbReferences, $this->resReferences, $this->persistenceUnitReferences);
    }

    /**
     * Initializes a bean configuration instance with the references of the passed deployment descriptor node.
     *
     * @param \SimpleXmlElement $node The deployment node with the bean configuration
     *
     * @return void
     */
    public function referencesFromDeploymentDescriptor(\SimpleXMLElement $node)
    {

        // initialize the enterprise bean references
        foreach ($node->xpath('a:epb-ref') as $epbReference) {
            $this->addEpbReference(EpbReferenceDescriptor::newDescriptorInstance()->fromDeploymentDescriptor($epbReference));
        }

        // initialize the resource references
        foreach ($node->xpath('a:res-ref') as $resReference) {
            $this->addResReference(ResReferenceDescriptor::newDescriptorInstance()->fromDeploymentDescriptor($resReference));
        }

        // initialize the resource references
        foreach ($node->xpath('a:persistence-unit-ref') as $persistenceUnitReference) {
            $this->addPersistenceUnitReference(PersistenceUnitReferenceDescriptor::newDescriptorInstance()->fromDeploymentDescriptor($persistenceUnitReference));
        }
    }

    /**
     * Initializes the bean configuration instance with the references of the passed reflection class instance.
     *
     * @param \AppserverIo\Lang\Reflection\ClassInterface $reflectionClass The reflection class with the bean configuration
     *
     * @return void
     */
    public function referencesFromReflectionClass(ClassInterface $reflectionClass)
    {

        // we've to check for property annotations that references EPB or resources
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            // load the EPB references
            if ($epbReference = EpbReferenceDescriptor::newDescriptorInstance()->fromReflectionProperty($reflectionProperty)) {
                $this->addEpbReference($epbReference);
            }

            // load the resource references
            if ($resReference = ResReferenceDescriptor::newDescriptorInstance()->fromReflectionProperty($reflectionProperty)) {
                $this->addResReference($resReference);
            }

            // load the persistence unit references
            if ($persistenceUnitReference = PersistenceUnitReferenceDescriptor::newDescriptorInstance()->fromReflectionProperty($reflectionProperty)) {
                $this->addPersistenceUnitReference($persistenceUnitReference);
            }
        }

        // we've to check for method annotations that references EPB or resources
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            // load the EPB references
            if ($epbReference = EpbReferenceDescriptor::newDescriptorInstance()->fromReflectionMethod($reflectionMethod)) {
                $this->addEpbReference($epbReference);
            }

            // load the resource references
            if ($resReference = ResReferenceDescriptor::newDescriptorInstance()->fromReflectionMethod($reflectionMethod)) {
                $this->addResReference($resReference);
            }

            // load the persistence unit references
            if ($persistenceUnitReference = PersistenceUnitReferenceDescriptor::newDescriptorInstance()->fromReflectionMethod($reflectionMethod)) {
                $this->addPersistenceUnitReference($persistenceUnitReference);
            }
        }
    }
}
