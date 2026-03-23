<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Http\Controller;

use BusinessG\BaseExcel\Exception\ExcelErrorCode;
use BusinessG\BaseExcel\Service\ExcelBusinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller
{
    public function __construct(protected ExcelBusinessService $service)
    {
    }

    public function export(Request $request): JsonResponse|StreamedResponse
    {
        $businessId = $request->input('business_id', '');
        if (!$businessId) {
            return response()->json($this->service->errorResponse(ExcelErrorCode::BUSINESS_ID_REQUIRED, 'business_id 必填'));
        }

        $result = $this->service->exportByBusinessId(
            $businessId,
            $request->input('param', [])
        );

        if ($result['response'] instanceof StreamedResponse) {
            return $result['response'];
        }

        return response()->json($this->service->successResponse($result));
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'business_id' => 'required|string',
            'url' => 'required|string',
        ]);

        $result = $this->service->importByBusinessId(
            $request->input('business_id'),
            $request->input('url'),
            $request->input('param', [])
        );

        return response()->json($this->service->successResponse(['token' => $result['token']]));
    }

    public function progress(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        return response()->json(
            $this->service->successResponse(
                $this->service->getProgressArrayByToken($request->input('token'))
            )
        );
    }

    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        return response()->json(
            $this->service->successResponse(
                $this->service->getMessageByToken($request->input('token'))
            )
        );
    }

    public function info(Request $request): JsonResponse
    {
        $request->validate([
            'business_id' => 'required|string',
        ]);

        return response()->json(
            $this->service->successResponse(
                $this->service->getInfoByBusinessId($request->input('business_id'))
            )
        );
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $uploadDir = config('excel.http.upload.dir', 'excel-import');
        $uploadDisk = config('excel.http.upload.disk', 'local');
        $path = $file->storeAs(
            $uploadDir . '/' . date('Y/m/d'),
            Str::random(40) . '.' . $file->getClientOriginalExtension(),
            $uploadDisk
        );

        $fullPath = Storage::disk($uploadDisk)->path($path);

        return response()->json(
            $this->service->successResponse([
                'path' => $fullPath,
                'url' => $fullPath,
            ])
        );
    }
}
