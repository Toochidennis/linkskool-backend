<?php

namespace V3\App\Controllers\Portal;

use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Attendance;
use V3\App\Traits\ValidationTrait;

class AttendanceController extends BaseController{
    private Attendance $attendance;
    use ValidationTrait;

    public function __construct(){
        parent::__construct();
        $this->initialize();
    }

    private function initialize(){

    }

    public function addAttendance(){
        
    }
    
}