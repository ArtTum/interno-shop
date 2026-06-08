<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\AccountingSetting\AccountingSettingFetchRequest;
use App\Services\ERP\Accounting\File\AccountingFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccountingFileController extends Controller
{
    private AccountingFileService $service;

    public function __construct(AccountingFileService $service)
    {
        $this->service = $service;
    }

    public function fetch(AccountingSettingFetchRequest $request): JsonResponse
    {
        return $this->service->fetch($request->validated());
    }

    public function download(Request $request): BinaryFileResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        return $this->service->download($request->all(), false);
    }
}
