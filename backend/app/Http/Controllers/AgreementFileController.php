<?php

namespace App\Http\Controllers;

use App\Models\AgreementFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgreementFileController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AgreementFile::with('file')->get());
    }

    public function attach(Request $request): JsonResponse
    {
        $request->validate([
            'parent_type_id' => 'required|exists:agreement_types,id',
            'parent_id' => 'required|integer',
            'file_id' => 'required|exists:files,id',
        ]);

        $agreementFile = AgreementFile::create($request->all());

        return response()->json($agreementFile, 201);
    }

    public function detach(AgreementFile $agreementFile): JsonResponse
    {
        $agreementFile->delete();

        return response()->json(['message' => 'File detached from agreement.']);
    }
}
