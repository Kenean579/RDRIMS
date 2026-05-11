<?php

namespace App\Http\Controllers;

use App\Models\AgreementType;
use App\Models\AgreementFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgreementFileController extends Controller
{
    public function attach(Request $request, $parentTypeName, $parentId): JsonResponse
    {
        $agreementType = AgreementType::where('name', $parentTypeName)->firstOrFail();

        $parentClass = match($agreementType->name) {
            'mo_u'    => \App\Models\MoU::class,
            'license' => \App\Models\License::class,
            default   => abort(400, 'Invalid agreement type.')
        };

        $parent = $parentClass::findOrFail($parentId);

        if ($agreementType->name === 'mo_u') {
            $this->authorize('update', $parent->partner);
        } else {
            $this->authorize('update', $parent->patent);
        }

        $request->validate(['file_id' => 'required|exists:files,id']);

        AgreementFile::create([
            'parent_type_id' => $agreementType->id,
            'parent_id'      => $parent->id,
            'file_id'        => $request->file_id,
        ]);

        return response()->json(['message' => 'File attached.']);
    }

    public function detach($parentTypeName, $parentId, $fileId): JsonResponse
    {
        $agreementType = AgreementType::where('name', $parentTypeName)->firstOrFail();

        $parentClass = match($agreementType->name) {
            'mo_u'    => \App\Models\MoU::class,
            'license' => \App\Models\License::class,
            default   => abort(400, 'Invalid agreement type.')
        };

        $parent = $parentClass::findOrFail($parentId);

        if ($agreementType->name === 'mo_u') {
            $this->authorize('update', $parent->partner);
        } else {
            $this->authorize('update', $parent->patent);
        }

        AgreementFile::where([
            'parent_type_id' => $agreementType->id,
            'parent_id'      => $parent->id,
            'file_id'        => $fileId,
        ])->delete();

        return response()->json(['message' => 'File detached.']);
    }
}