<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\AccountingSetting\AccountingSettingFetchRequest;
use App\Services\ERP\Accounting\HsCodesSales\HsCodesSalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccountingHsCodesSalesController extends Controller
{
    private HsCodesSalesService $service;

    public function __construct(HsCodesSalesService $service)
    {
        $this->service = $service;
    }

    public function fetch(AccountingSettingFetchRequest $request): JsonResponse
    {
        $vendorKey = $request->header('VendorKey');
        return $this->service->fetch($request->validated(), $vendorKey);
    }

    public function download(Request $request): BinaryFileResponse
    {
        return $this->service->download($request->all());
    }
}
