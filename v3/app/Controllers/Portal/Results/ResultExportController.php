<?php

namespace V3\App\Controllers\Portal\Results;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Portal\Results\ResultExportService;

#[Group('/portal')]
class ResultExportController
{
    private ResultExportService $resultExport;

    public function __construct()
    {
        $pdo = DatabaseConnector::connect('aalmgzmy_linkskoo_ddljuniorate2');
        $this->resultExport = new ResultExportService($pdo);
    }

    #[Route('/send-results', 'POST', ['api'])]
    public function sendResults()
    {
        $this->resultExport->sendResults();
    }
}
