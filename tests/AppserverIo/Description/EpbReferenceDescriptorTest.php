<?php

/**
 * AppserverIo\Description\EpbReferenceDescriptorTest
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

use AppserverIo\Lang\Reflection\ReflectionClass;
use AppserverIo\Lang\Reflection\ReflectionMethod;
use AppserverIo\Lang\Reflection\ReflectionProperty;
use AppserverIo\Psr\EnterpriseBeans\Annotations\EnterpriseBean;

/**
 * Test implementation for the EpbReferenceDescriptor class implementation.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/description
 * @link      http://www.appserver.io
 */
class EpbReferenceDescriptorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The descriptor instance we want to test.
     *
     * @var \AppserverIo\Description\EpbReferenceDescriptor
     */
    protected $descriptor;

    /**
     * Dummy bean reference.
     *
     * @EnterpriseBean(name="SessionBean")
     */
    protected $dummyEnterpriseBean;

    /**
     * Dummy resource reference.
     *
     * @Resource(name="Application")
     */
    protected $dummyResource;

    /**
     * Initializes the descriptor instance we want to test.
     *
     * @return void
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->descriptor = new EpbReferenceDescriptor();
    }

    /**
     * Injects the dummy bean instance.
     *
     * @param mixed $dummyEnterpriseBean The dummy bean
     *
     * @return void
     * @EnterpriseBean(name="SessionBean")
     */
    public function injectDummyEnterpriseBean($dummyEnterpriseBean)
    {
        $this->dummyEnterpriseBean = $dummyEnterpriseBean;
    }

    /**
     * Injects the dummy resource instance.
     *
     * @param mixed $dummyResource The dummy resource
     *
     * @return void
     * @Resource(name="Application")
     */
    public function injectDummyResource($dummyResource)
    {
        $this->dummyResource = $dummyResource;
    }

    /**
     * Tests the static newDescriptorInstance() method.
     *
     * @return void
     */
    public function testNewDescriptorInstance()
    {
        $this->assertInstanceOf(
            'AppserverIo\Description\EpbReferenceDescriptor',
            EpbReferenceDescriptor::newDescriptorInstance()
        );
    }

    /**
     * Test that the fromReflectionClass() methods has not yet been implemented.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testFromReflectionClass()
    {
        $this->descriptor->fromReflectionClass(new ReflectionClass('\stdClass'));
    }

    /**
     * Test that initization with the fromReflectionMethod() method
     * works as expected.
     *
     * @return void
     */
    public function testFromReflectionMethod()
    {

        // prepare the annotation values
        $values = array(
            'name' => 'SampleProcessor',
            'description' => 'A Description',
            'beanInterface' => 'SampleProcessorLocal',
            'beanName' => 'SampleProcessor',
            'lookup' => 'php:global/example/SampleProcessor'
        );

        // create a mock annotation implementation
        $beanAnnotation = $this->getMockBuilder('AppserverIo\Psr\EnterpriseBeans\Annotations\EnterpriseBean')
            ->setConstructorArgs(array('EnterpriseBean', $values))
            ->getMockForAbstractClass();

        // create a mock annotation
        $annotation = $this->getMockBuilder('AppserverIo\Lang\Reflection\ReflectionAnnotation')
            ->setMethods(array('getAnnotationName', 'getValues', 'newInstance'))
            ->setConstructorArgs(array('EnterpriseBean', $values))
            ->getMock();

        // mock the ReflectionAnnotation methods
        $annotation
            ->expects($this->once())
            ->method('getAnnotationName')
            ->will($this->returnValue('EnterpriseBean'));
        $annotation
            ->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($values));
        $annotation
            ->expects($this->once())
            ->method('newInstance')
            ->will($this->returnValue($beanAnnotation));


        // initialize the annotation aliases
        $aliases = array(EnterpriseBean::ANNOTATION => EnterpriseBean::__getClass());

        // initialize the reflection method
        $reflectionMethod = $this->getMockBuilder('AppserverIo\Lang\Reflection\ReflectionMethod')
                                 ->setConstructorArgs(array(__CLASS__, array(), $aliases))
                                 ->setMethods(array('hasAnnotation', 'getAnnotation'))
                                 ->getMock();

        // mock the methods
        $reflectionMethod
            ->expects($this->once())
            ->method('hasAnnotation')
            ->with(EnterpriseBean::ANNOTATION)
            ->will($this->returnValue(true));
        $reflectionMethod
            ->expects($this->once())
            ->method('getAnnotation')
            ->with(EnterpriseBean::ANNOTATION)
            ->will($this->returnValue($annotation));

        // initialize the descriptor from the reflection method
        $this->descriptor->fromReflectionMethod($reflectionMethod);

        // check that the descriptor has been initialized successfully
        $this->assertSame('env/SampleProcessor', $this->descriptor->getName());
        $this->assertSame('A Description', $this->descriptor->getDescription());
        $this->assertSame('SampleProcessorLocal', $this->descriptor->getBeanInterface());
        $this->assertSame('SampleProcessor', $this->descriptor->getBeanName());
        $this->assertSame('php:global/example/SampleProcessor', $this->descriptor->getLookup());
    }

    /**
     * Test that initization with the fromReflectionMethod() method
     * and an empty annotation works as expected.
     *
     * @return void
     */
    public function testFromReflectionMethodAndAnnotationWithoutAttributes()
    {

        // prepare the empty annotation values
        $values = array();

        // create a mock annotation implementation
        $beanAnnotation = $this->getMockBuilder('AppserverIo\Psr\EnterpriseBeans\Annotations\EnterpriseBean')
            ->setConstructorArgs(array('EnterpriseBean', $values))
            ->getMockForAbstractClass();

        // create a mock annotation
        $annotation = $this->getMockBuilder('AppserverIo\Lang\Reflection\ReflectionAnnotation')
            ->setMethods(array('getAnnotationName', 'getValues', 'newInstance'))
            ->setConstructorArgs(array('EnterpriseBean', $values))
            ->getMock();

        // mock the ReflectionAnnotation methods
        $annotation
            ->expects($this->once())
            ->method('getAnnotationName')
            ->will($this->returnValue('EnterpriseBean'));
        $annotation
            ->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($values));
        $annotation
            ->expects($this->once())
            ->method('newInstance')
            ->will($this->returnValue($beanAnnotation));


        // initialize the annotation aliases
        $aliases = array(EnterpriseBean::ANNOTATION => EnterpriseBean::__getClass());

        // initialize the reflection method
        $reflectionMethod = $this->getMockBuilder('AppserverIo\Lang\Reflection\ReflectionMethod')
                                 ->setConstructorArgs(array(__CLASS__, array(), $aliases))
                                 ->setMethods(
                                     array(
                                         'hasAnnotation',
                                         'getAnnotation',
                                         'getClassName',
                                         'getMethodName'
                                     )
                                 )
                                 ->getMock();

        // mock the methods
        $reflectionMethod
            ->expects($this->once())
            ->method('hasAnnotation')
            ->with(EnterpriseBean::ANNOTATION)
            ->will($this->returnValue(true));
        $reflectionMethod
            ->expects($this->once())
            ->method('getAnnotation')
            ->with(EnterpriseBean::ANNOTATION)
            ->will($this->returnValue($annotation));
        $reflectionMethod
            ->expects($this->exactly(2))
            ->method('getClassName')
            ->will($this->returnValue(__CLASS__));
        $reflectionMethod
            ->expects($this->exactly(2))
            ->method('getMethodName')
            ->will($this->returnValue('injectDummyEnterpriseBean'));

        // initialize the descriptor from the reflection method
        $this->descriptor->fromReflectionMethod($reflectionMethod);

        // check that the descriptor has been initialized successfully
        $this->assertSame('env/DummyEnterpriseBean', $this->descriptor->getName());
        $this->assertSame('DummyEnterpriseBeanLocal', $this->descriptor->getBeanInterface());
        $this->assertSame('DummyEnterpriseBean', $this->descriptor->getBeanName());
        $this->assertNull($this->descriptor->getDescription());
        $this->assertNull($this->descriptor->getLookup());
    }

    /**
     * Test that initization with the fromReflectionProperty() method
     * and an empty annotation works as expected.
     *
     * @return void
     */
    public function testFromReflectionPropertyAndAnnotationWithSomeAttributes()
    {

        // prepare the annotation values
        $values = array(
            'description' => 'A Description',
            'beanInterface' => 'DummyEnterpriseBeanLocal',
            'beanName' => 'DummyEnterpriseBean',
            'lookup' => 'php:global/example/DummyEnterpriseBean'
        );

        // create a mock annotation implementation
        $beanAnnotation = $this->getMockBuilder('AppserverIo\Psr\EnterpriseBeans\Annotations\EnterpriseBean')
            ->setConstructorArgs(array('EnterpriseBean', $values))
            ->getMockForAbstractClass();

        // create a mock annotation
        $annotation = $this->getMockBuilder('AppserverIo\Lang\Reflection\ReflectionAnnotation')
            ->setMethods(array('getAnnotationName', 'getValues', 'newInstance'))
            ->setConstructorArgs(array('EnterpriseBean', $values))
            ->getMock();

        // mock the ReflectionAnnotation methods
        $annotation
            ->expects($this->once())
            ->method('getAnnotationName')
            ->will($this->returnValue('EnterpriseBean'));
        $annotation
            ->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($values));
        $annotation
            ->expects($this->once())
            ->method('newInstance')
            ->will($this->returnValue($beanAnnotation));


        // initialize the annotation aliases
        $aliases = array(EnterpriseBean::ANNOTATION => EnterpriseBean::__getClass());

        // initialize the reflection property
        $reflectionProperty = $this->getMockBuilder('AppserverIo\Lang\Reflection\ReflectionProperty')
                                   ->setConstructorArgs(array(__CLASS__, array(), $aliases))
                                   ->setMethods(
                                       array(
                                           'hasAnnotation',
                                           'getAnnotation',
                                           'getClassName',
                                           'getPropertyName'
                                       )
                                   )
                                   ->getMock();

        // mock the methods
        $reflectionProperty
            ->expects($this->once())
            ->method('hasAnnotation')
            ->with(EnterpriseBean::ANNOTATION)
            ->will($this->returnValue(true));
        $reflectionProperty
            ->expects($this->once())
            ->method('getAnnotation')
            ->with(EnterpriseBean::ANNOTATION)
            ->will($this->returnValue($annotation));
        $reflectionProperty
            ->expects($this->exactly(1))
            ->method('getClassName')
            ->will($this->returnValue(__CLASS__));
        $reflectionProperty
            ->expects($this->exactly(2))
            ->method('getPropertyName')
            ->will($this->returnValue('dummyEnterpriseBean'));

        // initialize the descriptor from the reflection property
        $this->descriptor->fromReflectionProperty($reflectionProperty);

        // check that the descriptor has been initialized successfully
        $this->assertSame('env/DummyEnterpriseBean', $this->descriptor->getName());
        $this->assertSame('DummyEnterpriseBeanLocal', $this->descriptor->getBeanInterface());
        $this->assertSame('DummyEnterpriseBean', $this->descriptor->getBeanName());
        $this->assertSame('A Description', $this->descriptor->getDescription());
        $this->assertSame('php:global/example/DummyEnterpriseBean', $this->descriptor->getLookup());
    }

    /**
     * Initializes the descriptor from a deployment descriptor.
     *
     * @return void
     */
    public function testFromDeploymentDescriptor()
    {

        // load the deployment descriptor node
        $node = new \SimpleXMLElement(file_get_contents(__DIR__ . '/_files/dd-epb-ref.xml'));

        // initialize the descriptor from the nodes data
        $this->descriptor->fromDeploymentDescriptor($node);

        // check if all values have been initialized
        $this->assertSame('env/UserProcessor', $this->descriptor->getName());
        $this->assertSame('Some Description', $this->descriptor->getDescription());
        $this->assertSame('php:global/example/UserProcessor', $this->descriptor->getLookup());
        $this->assertSame('UserProcessorLocal', $this->descriptor->getBeanInterface());
        $this->assertSame('UserProcessor', $this->descriptor->getBeanName());
        $this->assertInstanceOf('AppserverIo\Description\InjectionTargetDescriptor', $this->descriptor->getInjectionTarget());
    }

    /**
     * Tests that initialization from an invalid deployment descriptor won't work.
     *
     * @return void
     */
    public function testFromDeploymentDescriptorInvalid()
    {

        // load the deployment descriptor node
        $node = new \SimpleXMLElement(file_get_contents(__DIR__ . '/_files/dd-messagedrivenbean.xml'));

        // check that the descriptor has not been initialized
        $this->assertNull($this->descriptor->fromDeploymentDescriptor($node));
    }

    /**
     * Tests if the merge method works successfully.
     *
     * @return void
     */
    public function testMergeSuccessful()
    {

        // load the deployment descriptor node
        $node = new \SimpleXMLElement(file_get_contents(__DIR__ . '/_files/dd-epb-ref.xml'));

        // initialize the descriptor from the nodes data
        $this->descriptor->fromDeploymentDescriptor($node);

        // initialize the descriptor to merge
        $descriptorToMerge = $this->getMockForAbstractClass('AppserverIo\Description\EpbReferenceDescriptor');
        $nodeToMerge = new \SimpleXMLElement(file_get_contents(__DIR__ . '/_files/dd-epb-ref-to-merge.xml'));
        $descriptorToMerge->fromDeploymentDescriptor($nodeToMerge);

        // merge the descriptors
        $this->descriptor->merge($descriptorToMerge);

        // check if all values have been initialized
        $this->assertSame('env/MyUserProcessor', $this->descriptor->getName());
        $this->assertSame('Another Description', $this->descriptor->getDescription());
        $this->assertSame('php:global/example/MyUserProcessor', $this->descriptor->getLookup());
        $this->assertSame('MyUserProcessorLocal', $this->descriptor->getBeanInterface());
        $this->assertSame('MyUserProcessor', $this->descriptor->getBeanName());
        $this->assertInstanceOf('AppserverIo\Description\InjectionTargetDescriptor', $injectTarget = $this->descriptor->getInjectionTarget());
        $this->assertSame('AppserverIo\Apps\Example\Services\ASampleProcessor', $injectTarget->getTargetClass());
        $this->assertSame('aSampleProcessor', $injectTarget->getTargetProperty());
        $this->assertNull($injectTarget->getTargetMethod());
    }
}
