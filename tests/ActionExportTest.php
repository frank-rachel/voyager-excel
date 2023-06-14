<?php

namespace FrankRachel\VoyagerExcel\Tests;

use FrankRachel\VoyagerExcel\Actions\Export;

class ActionExportTest extends TestCase
{
    public function testGetTitle()
    {
        $export = new Export('foo', 'bar');
        $reslut = $export->getTitle();

        $this->assertEquals($reslut, 'Export Excel');
    }

    public function testShouldActionDisplayOnDataType()
    {
        $dataType = new \stdClass();

        $export = new Export($dataType, 'bar');

        $this->assertEquals(
            $export->shouldActionDisplayOnDataType(),
            false
        );

        $dataType->model_name = 'foo';

        $this->assertEquals(
            $export->shouldActionDisplayOnDataType(),
            false
        );

        $dataType->model_name = 'FrankRachel\VoyagerExcel\Tests\Foo';
        $this->assertEquals(
            $export->shouldActionDisplayOnDataType(),
            false
        );
    }

    public function testGetDefaultRoute()
    {
        $export = new Export('foo', 'bar');
        $reslut = $export->getDefaultRoute();

        $this->assertEquals($reslut, null);
    }
}

class Foo
{
}
