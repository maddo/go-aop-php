<?php

namespace Go\Aop\Framework;

use Go\Stubs\FirstStatic;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-20 at 11:58:54.
 */
class StaticClosureMethodInvocationTest extends \PHPUnit_Framework_TestCase
{

    const FIRST_CLASS_NAME = 'Go\Stubs\First';

    protected static $invocationClass;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$invocationClass = MethodInvocationComposer::compose(true, true, false, false);

    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped("Closure Method Invocation works only on PHP 5.4 and greater");
        }
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("Skipped due to the bug https://github.com/facebook/hhvm/issues/1203");
        }
    }

    /**
     * Tests static method invocations with self
     *
     * @dataProvider staticSelfMethodsBatch
     */
    public function testStaticSelfMethodInvocation($methodName, $expectedResult)
    {
        $childClass = $this->getMockClass(self::FIRST_CLASS_NAME, array('none'));
        $invocation = new self::$invocationClass($childClass, $methodName, array());

        $result = $invocation($childClass);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests static method invocations with self not overridden with parent
     *
     * @dataProvider staticSelfMethodsBatch
     */
    public function testStaticSelfNotOverridden($methodName, $expectedResult)
    {
        $childClass = $this->getMockClass(self::FIRST_CLASS_NAME, array($methodName));
        $invocation = new self::$invocationClass(self::FIRST_CLASS_NAME, $methodName, array());

        $result = $invocation($childClass);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests static method invocations with Late Static Binding
     *
     * @dataProvider staticLsbMethodsBatch
     */
    public function testStaticLsbIsWorking($methodName)
    {
        $childClass = $this->getMockClass(self::FIRST_CLASS_NAME, array($methodName));
        $invocation = new self::$invocationClass(self::FIRST_CLASS_NAME, $methodName, array());

        $result = $invocation($childClass);
        $this->assertEquals($childClass, $result);
    }

    public function testValueChangedByReference()
    {
        $child      = $this->getMock(self::FIRST_CLASS_NAME, array('none'));
        $invocation = new self::$invocationClass(self::FIRST_CLASS_NAME, 'staticPassByReference', array());

        $value  = 'test';
        $result = $invocation($child, array(&$value));
        $this->assertEquals(null, $result);
        $this->assertEquals(null, $value);
    }

    public function testRecursionWorks()
    {
        $invocation = new self::$invocationClass(self::FIRST_CLASS_NAME, 'staticLsbRecursion', array());
        $child      = new FirstStatic($invocation);

        $childClass = get_class($child);
        $this->assertEquals(5, $childClass::staticLsbRecursion(5,0));
        $this->assertEquals(20, $childClass::staticLsbRecursion(5,3));
    }

    public function testAdviceIsCalledForInvocation()
    {
        $child  = $this->getMock(self::FIRST_CLASS_NAME, array('none'));
        $value  = 'test';
        $advice = new BeforeInterceptor(function () use (&$value) {
            $value = 'ok';
        });

        $invocation = new self::$invocationClass(self::FIRST_CLASS_NAME, 'staticSelfPublic', array($advice));

        $result = $invocation($child, array());
        $this->assertEquals('ok', $value);
        $this->assertEquals(T_PUBLIC, $result);
    }

    public function testInvocationWithDynamicArguments()
    {
        $child      = $this->getMock(self::FIRST_CLASS_NAME, array('none'));
        $invocation = new self::$invocationClass(self::FIRST_CLASS_NAME, 'staticVariableArgsTest', array());

        $args     = array();
        $expected = '';
        for ($i=0; $i<10; $i++) {
            $args[]   = $i;
            $expected .= $i;
            $result   = $invocation($child, $args);
            $this->assertEquals($expected, $result);
        }
    }

    public function staticSelfMethodsBatch()
    {
        return array(
            array('staticSelfPublic', T_PUBLIC),
            array('staticSelfProtected', T_PROTECTED),
            array('staticSelfPublicAccessPrivate', T_PRIVATE),
        );
    }

    public function staticLsbMethodsBatch()
    {
        return array(
            array('staticLsbPublic'),
            array('staticLsbProtected'),
        );
    }

}
