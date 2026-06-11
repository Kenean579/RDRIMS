<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Permission;

$user = User::where('email', 'nested_admin@example.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "Testing Permission Resolution for: " . $user->name . " (ID: " . $user->id . ")\n";
echo "Context: University: " . $user->university_id . ", Campus: " . ($user->campus_id ?? 'None') . ", Faculty: " . ($user->faculty_id ?? 'None') . ", Dept: " . $user->department_id . ", RC: " . $user->research_center_id . "\n";

$permIds = $user->getEffectivePermissionIds();
$perms = Permission::whereIn('id', $permIds)->pluck('name')->toArray();

echo "\nEffective Permissions (from hierarchy):\n";
foreach ($perms as $p) {
    echo " - $p\n";
}

// Verification
$expected = ['view_proposals', 'submit_proposals'];
$unexpected = ['view_all_proposals'];

$success = true;
foreach ($expected as $e) {
    if (!in_array($e, $perms)) {
        echo "❌ MISSING EXPECTED PERM: $e\n";
        $success = false;
    }
}
foreach ($unexpected as $u) {
    if (in_array($u, $perms)) {
        echo "❌ UNEXPECTED PERM FOUND: $u (Should have been revoked by Research Center override)\n";
        $success = false;
    }
}

if ($success) {
    echo "\n✅ HIERARCHICAL PERMISSION RESOLUTION VERIFIED!\n";
} else {
    echo "\n❌ VERIFICATION FAILED.\n";
}
