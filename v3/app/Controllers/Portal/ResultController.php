<?php

namespace V3\App\Controllers\Portal;

use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Result;
use V3\App\Traits\ValidationTrait;

class ResultController extends BaseController{
    private Result $result;
    use ValidationTrait;
    
    public function __construct(){
        parent::__construct();
    }

    

}