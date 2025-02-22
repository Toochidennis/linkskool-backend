<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Assessment;
use V3\App\Services\Portal\AssessmentService;

class AssessmentController{
    private Assessment $assessment;
    private AssessmentService $assessmentService;
    private array $post;
    private array $response = ['success'=>false];

    public function __construct(){
        $this->initialize();
    }

    private function initialize(){

    }

    public function addAssessment(){
        
    }

    public function updateAssessment(){

    }

    public function fetchAssessments(array $params){

    }

    public function deleteAssessment(array $params){

    }
}