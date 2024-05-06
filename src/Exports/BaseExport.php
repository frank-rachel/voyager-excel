<?php

namespace FrankRachel\VoyagerExcel\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\View;
use TCG\Voyager\Facades\Voyager;

class BaseExport extends AbstractExport implements FromCollection
{
    protected $dataType;
    protected $model;
    protected $ids;

    public function __construct($dataType, array $ids)
    {
		set_time_limit(300);
        $this->dataType = $dataType;
        $this->model = new $dataType->model_name();
        $this->ids = array_filter($ids);
    }
	
	public static function voyagertranslate(&$resultset, $lang='') { 
			if (Voyager::translatable($resultset)) {
				if ($lang=='') {
					// echo ('*tr*'.\LaravelLocalization::getCurrentLocale());
					$resultset=$resultset->translate(\LaravelLocalization::getCurrentLocale(), 'nl');
				} else {
					// echo ('*TR*'.\LaravelLocalization::getCurrentLocale().$lang);
					$resultset=$resultset->translate($lang);
				}
			}	
	}
	
    public function collection()
    {
		set_time_limit(300);
		//updated for relationships
		$rr=$this->dataType->readRows;
		$this->voyagertranslate($rr);
        $fields = $rr->map(function ($res) {
            return $res['field'];
        });

        $table = $rr->map(function ($res) {
            return $res['display_name'];
        });
		
        $rs = $this->model->when(
            count($this->ids) > 0,
            function ($query) {
                $query->whereIn($this->model->getKeyName(), $this->ids);
            }
        )->get();

        $rs = $rs->map(function ($res) use ($fields) {
            $arr = [];
            foreach ($this->dataType->readRows as $row) {
				$val=$row->field;
                $arr[$val] = $res[$val];
                $arr[$val] = $row->type.' '.$res[$val];
				// print_r($res);
				// exit;
				if($row->type == 'relationship') {
					$output = View::make('voyager::formfields.relationship', [
						'view' => 'browse',
						'row' => $row,
						'data' => $res,
						'dataTypeContent' => $res,
						'options' => $row->details
					])->render();
					$arr[$val] = strip_tags($output);
				}
            }

            return $arr;
        });

        $table = collect([$table->toArray()])->merge($rs);

        return $table;
    }
}
