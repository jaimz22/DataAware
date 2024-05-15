<?php

namespace VertigoLabs\Tests\DataAware;

use VertigoLabs\DataAware\DataAware;
use PHPUnit\Framework\TestCase;
use VertigoLabs\DataAware\DataAwareInterface;
use VertigoLabs\DataAware\Exceptions\DataNotFoundNoDefaultException;

class DataAwareTest extends TestCase
{

    public function testDefineDataDefaults()
    {
        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $refl = new \ReflectionObject($obj);
        $method = $refl->getMethod('defineDataDefaults');
        $method->setAccessible(true);

        $this->assertIsArray($method->invoke($obj));
    }

    public function testSettingDefaults()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
        ];
        $obj = $this->getMockForAbstractClass(TestDataAware::class,[],'',true,true,true, ['defineDataDefaults']);
        $method = $obj->method('defineDataDefaults');
        $method->willReturn([
                    'testThree' => 'testThree',
                    'testFour' => [
                        'nestedOne' => 'test four.nested one'
                    ]
                ]
        );

        $obj->setData($testData, true, true);

        $this->assertArrayHasKey('testOne', $obj->getData());
        $this->assertArrayHasKey('testTwo', $obj->getData());
        $this->assertArrayHasKey('testThree', $obj->getData());
        $this->assertArrayHasKey('testFour', $obj->getData());
        $this->assertArrayHasKey('nestedOne', $obj->getData('testFour'));
    }

    public function testMergeData()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);
        $this->assertArrayHasKey('testOne', $obj->getData());
        $this->assertArrayHasKey('testTwo', $obj->getData());
        $this->assertArrayHasKey('test-two', $obj->getRawData());

        $obj->mergeData([
            'test five'=>'5',
            'test six'=>[
                'nestedOne'=>'6-1',
            ]
        ]);

        $this->assertSame($obj->getData('testSix.nestedOne'), '6-1');
    }

    public function testGetData()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->assertArrayHasKey('nestedOne', $obj->getData('testFour'));
        $this->assertSame($obj->getData('testFour.nestedOne'), '4-1');
        $this->assertSame([
            'one'=>'1',
            'two'=>'2',
            'three'=>'4-1'
        ], $obj->getData([
            'one'=>'testOne',
            'two'=>'testTwo',
            'three'=>'testFour.nestedOne'
        ]));
    }

    public function testGetDataComplexAccesser()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->assertSame($obj->getData('[testFour][nestedOne]'), '4-1');
    }

    public function testGetDataFailure()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->expectException(DataNotFoundNoDefaultException::class);
        $obj->getData('does-not-exist');
    }

    public function testGetDataNestedFailure()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->expectException(DataNotFoundNoDefaultException::class);
        $obj->getData('testFour.does-not-exist');
    }

    public function testGetDataDefault()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->assertSame('default-test',$obj->getData('does-not-exist', 'default-test'));
    }

    public function testGetRawData()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->assertArrayHasKey('nested one', $obj->getRawData('test four'));
        $this->assertSame($obj->getRawData('test four.nested one'), '4-1');
        $this->assertSame([
            'one'=>'1',
            'two'=>'2',
            'three'=>'4-1'
        ], $obj->getRawData([
            'one'=>'test one',
            'two'=>'test-two',
            'three'=>'test four.nested one'
        ]));
    }

    public function testGetRawDataFailure()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->expectException(DataNotFoundNoDefaultException::class);
        $obj->getRawData('does-not-exist');
    }

    public function testGetRawDataDefault()
    {
        $testData = [
            'test one'=>'1',
            'test-two'=>'2',
            'testThree'=>'3',
            'test four' => [
                'nested one'=>'4-1'
            ]
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        $this->assertSame('default-test',$obj->getRawData('does-not-exist', 'default-test'));
    }

    public function testHasData()
    {
        $testData = [
            'test one'=>'testOne',
            'test-two'=>'testTwo',
            'testThree'=>'testThree',
            ' test four '=>'testFour',
            'Test five'=>'testFive',
            'test SIX'=> 'testSIX',
            'test.seven'=> 'testSeven'
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        foreach ($testData as $key=>$value) {
            $this->assertTrue($obj->hasData($value));
        }
        $this->assertFalse($obj->hasData('does-not-exist'));
        $this->assertFalse($obj->hasData('test one'));
    }

    public function testHasRawData()
    {
        $testData = [
            'test one'=>'testOne',
            'test-two'=>'testTwo',
            'testThree'=>'testThree',
            ' test four '=>'testFour',
            'Test five'=>'testFive',
            'test SIX'=> 'testSIX',
            'test.seven'=> 'testSeven'
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        foreach ($testData as $key=>$value) {
            $this->assertTrue($obj->hasRawData($key));
        }
        $this->assertFalse($obj->hasRawData('does-not-exist'));
        $this->assertFalse($obj->hasRawData('testOne'));
    }

    public function testSetData()
    {
        $testData = [
            'test one'=>'testOne',
            'test-two'=>'testTwo',
            'testThree'=>'testThree',
            ' test four '=>'testFour',
            'Test five'=>'testFive',
            'test SIX'=> 'testSIX',
            'test.seven'=> 'testSeven'
        ];

        $obj = $this->getMockForAbstractClass(TestDataAware::class);
        $obj->setData($testData);

        foreach ($testData as $key=>$value) {
            $this->assertArrayHasKey($value, $obj->getData());
            $this->assertArrayHasKey($key, $obj->getRawData());
        }
    }
}
