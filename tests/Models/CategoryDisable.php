<?php

namespace FrankRachel\VoyagerExcel\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryDisable extends Model
{
    public $disable_export = true;

    protected $table = 'categorydisables';

    protected $fillable = ['slug', 'name'];
}
