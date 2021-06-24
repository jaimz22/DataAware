<?php

namespace VertigoLabs\Tests\DataAware;

use VertigoLabs\DataAware\DataAware;
use PHPUnit\Framework\TestCase;
use VertigoLabs\DataAware\DataAwareInterface;

class DataAwareTest extends TestCase
{

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
