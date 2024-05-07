<?php

namespace FrankRachel\VoyagerExcel\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\View;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Auth;


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

		// Fetch readRows and apply translations
		$rr = $this->dataType->readRows;
		$this->voyagertranslate($rr);

		// Filter readRows based on the adminlevel before mapping to fields and table
		$filteredRows = collect($rr)->filter(function ($row) {
			$options = json_decode($row->details);
			if (isset($options->adminlevel)) {
				return Auth::user()->hasRole($options->adminlevel);
			}
			return true; // If no adminlevel is set, the row is visible
		});

		// Map the filtered readRows to fields
		$fields = $filteredRows->map(function ($row) {
			return $row->field;
		});

		// Map the filtered readRows to table display names
		$table = $filteredRows->map(function ($row) {
			return $row->display_name;
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
				set_time_limit(300);
				$options = ($row->details);
				$visible=true;
				if(isset($options->adminlevel)) {
					if (!(Auth::user()->hasRole($options->adminlevel))) {
						$visible=false;
					}
				}
				if ($visible) {				
					$val=$row->field;
					$arr[$val] = $res[$val];
					
					// print_r($res);
					// exit;
					if ($row->type == 'timestamp' && $arr[$val] <> '') {
						if ($res[$val] instanceof \Illuminate\Support\Carbon) {
							$arr[$val] = $res[$val]->format('d/m/Y');
						} else if (is_numeric($res[$val])) {
							$arr[$val] = date('d/m/Y', $res[$val]);
						}
					}

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
            }

            return $arr;
        });

		// Merge the table display names with the resulting set
		$table = collect([$table->toArray()])->merge($rs);
		return $table;
		
		
	}

}
