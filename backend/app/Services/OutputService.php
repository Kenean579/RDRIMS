<?php

namespace App\Services;

use App\Models\Output;
use App\Models\OutputStatus;

class OutputService
{
    public function changeStatus(Output $output, $newStatusIdOrName): Output
    {
        $currentStatus = $output->status->name;

        if (is_numeric($newStatusIdOrName)) {
            $newStatus = OutputStatus::findOrFail($newStatusIdOrName);
            $newStatusName = $newStatus->name;
        } else {
            $newStatusName = $newStatusIdOrName;
            $newStatus = OutputStatus::where('name', $newStatusName)->firstOrFail();
        }

        $allowedTransitions = [
            'draft'                   => ['submitted'],
            'submitted'               => ['approved_by_supervisor', 'rejected'],
            'approved_by_supervisor'  => ['approved', 'rejected'],
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            // Allow admin to bypass? No, let's stick to logic but maybe allow same-status updates
            if ($currentStatus === $newStatusName) return $output;
            abort(422, "Status '{$currentStatus}' cannot be changed.");
        }

        if (!in_array($newStatusName, $allowedTransitions[$currentStatus])) {
             if ($currentStatus === $newStatusName) return $output;
            abort(422, "Cannot change status from '{$currentStatus}' to '{$newStatusName}'.");
        }

        $output->update(['status_id' => $newStatus->id]);

        return $output->refresh();
    }
}