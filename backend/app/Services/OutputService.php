<?php

namespace App\Services;

use App\Models\Output;
use App\Models\OutputStatus;

class OutputService
{
    public function changeStatus(Output $output, string $newStatusName): Output
    {
        $currentStatus = $output->status->name;

        $allowedTransitions = [
            'draft'                   => ['submitted'],
            'submitted'               => ['approved_by_supervisor', 'rejected'],
            'approved_by_supervisor'  => ['approved', 'rejected'],
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            abort(422, "Status '{$currentStatus}' cannot be changed.");
        }

        if (!in_array($newStatusName, $allowedTransitions[$currentStatus])) {
            abort(422, "Cannot change status from '{$currentStatus}' to '{$newStatusName}'.");
        }

        $newStatus = OutputStatus::where('name', $newStatusName)->firstOrFail();
        $output->update(['status_id' => $newStatus->id]);

        return $output->refresh();
    }
}