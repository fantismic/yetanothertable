<?php

namespace Fantismic\YetAnotherTable\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use WireUi\Traits\WireUiActions;

trait StateHandler
{

  public $handle_state = false;

  public function useStateHandler(Bool $bool) {
    $this->handle_state = $bool;
  }

  public function saveTableState() {
    if ($this->handle_state) {
      try {
        DB::table('yat_user_table_config')->updateOrInsert(
          ['user_id' => Auth::user()->id, 'table' => static::class],
          ['configuration' => json_encode($this->columns->pluck('isVisible','key'))]);
          
          $this->dispatch('tableStateSaved', true);
      } catch (\Throwable $th) {
        $this->dispatch('tableStateSaved', false);
      }
    }
  }

  public function setTableState() {
    if ($this->handle_state) {
      $state = DB::table('yat_user_table_config')->where(['user_id' => Auth::user()->id, 'table' => static::class])->first()->configuration ?? false;
      
      if ($state) {
        $state = json_decode($state,true);
        foreach ($state as $key => $isVisible) {
          $this->columns->where('key', $key)->first()->isVisible = $isVisible;
        }
      }
    }
  }

}