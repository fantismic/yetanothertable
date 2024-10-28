<?php

namespace Fantismic\YetAnotherTable\Traits;

trait RowManipulators
{

    public function removeRowFromTable($id) {
        $this->yatTableData = $this->yatTableData->reject(function ($item) use ($id) {
            return $item[$this->column_id] == $id;
        });
        $this->selected = [];
    }

}