<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Http\Controller;

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
            return response()->json($this->service->errorResponse(422, 'business_id 必填'), 422);
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
            $request->input('url')
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
        $path = $file->storeAs(
            'excel-import/' . date('Y/m/d'),
            Str::random(40) . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        $fullPath = Storage::disk('local')->path($path);

        return response()->json(
            $this->service->successResponse([
                'path' => $fullPath,
                'url' => $fullPath,
            ])
        );
    }
}
