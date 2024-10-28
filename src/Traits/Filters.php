<?php

namespace Fantismic\YetAnotherTable\Traits;

use Carbon\Carbon;

trait Filters
{

    public $filters;
    public $has_filters = false;
    public $show_filters = false;

    public function setFilters() {
        try {
            $this->filters = collect($this->filters());
            $this->filters = $this->filters->map(function ($item) {
                return (object) get_object_vars($item);
            });

            foreach ($this->filters as $filter) {
                if ($filter->column) {
                    $filter->key = $filter->column;
                } else {
                    $filter->key = $this->getColumnKey($filter->label);
                }
            }
            
            if (!$this->filters->isEmpty()) {
                $this->has_filters = true;
            }

        } catch (\Throwable $th) {
            $this->has_filters = false;
        }
    }

    public function getColumnKey($filter_label) {
        return $this->columns->where('label',$filter_label)->first()->key;
    }

    public function updatedFilters($key,$value) {
        if (!str_contains($key,'filters.')) {
            return;
        }
        $key = str_replace(array('filters.','.input'),'',$key);
        
        if ($this->filters[$key]->type == "string") {
            $this->filters[$key]->input = trim($this->filters[$key]->input);
        }
        if ($this->filters[$key]->type == "daterange") {
            if (empty(trim($value))) {
                $this->filters[$key]->input = null;
                $this->filters[$key]->daterange = null;
            }
            if (str_contains($this->filters[$key]->input, 'to')) {
                [$start, $end] = explode(' to ', trim($this->filters[$key]->input));
                $this->filters[$key]->daterange = ["start"=>Carbon::parse($start),"end"=>Carbon::parse($end)];
            }
        }
        
    }

    public function applyFilters($data) {
        if (!$this->has_filters) return $data;
        
        return $data->filter(function ($item) {
            foreach ($this->filters as $filter) {
                if ($filter->type == "string") {
                    if ($filter->input && !str_contains(strtolower($item[$filter->key]), strtolower($filter->input))) {
                        return false;
                    }
                }
                if ($filter->type == "daterange") {
                    if (isset($filter->daterange['start'], $filter->daterange['end'])) {
                        $itemDate = Carbon::parse($item[$filter->key]); // Assume your data has a 'date' field
            
                        if (!$itemDate->between($filter->daterange['start'], $filter->daterange['end'])) {
                            return false; // Exclude if the date is not in range
                        }
                    }
                }
            }
            return true;
        });
    }

}