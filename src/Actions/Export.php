<?php

namespace VoyagerExcel\Actions;

use TCG\Voyager\Actions\AbstractAction;
use Maatwebsite\Excel\Facades\Excel;
use VoyagerExcel\Exports\PostExport;
use Illuminate\Database\Eloquent\Model;

class Export extends AbstractAction
{
    public function getTitle()
    {
        return __('voyager_excel::excel.export_excel');
    }

    public function getIcon()
    {
        return 'voyager-list';
    }

    public function shouldActionDisplayOnDataType()
    {
        if(empty($this->dataType->model_name)){
            return false;
        }
        if(!class_exists($this->dataType->model_name)){
            return false;
        }
        $model = new $this->dataType->model_name;
        if(!($model instanceof  Model)){
            return false;
        }
        return true;
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary pull-right',
        ];
    }

    public function getDefaultRoute()
    {
        return null;
    }


    public function massAction($ids, $comingFrom)
    {
        if(empty(array_filter($ids))){
            return redirect($comingFrom);
        }
        return Excel::download(new PostExport($this->dataType, $ids), 'demo-'.date('H:i:s').'.xls');
    }
}