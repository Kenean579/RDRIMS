# Call Module - Post-Implementation Verification Checklist

**Purpose**: Verify Call module refactoring is complete and working correctly  
**Date**: July 22, 2026  
**Status**: Ready for verification

---

## Pre-Verification Setup

### Step 1: Prepare Environment
```bash
# Navigate to backend directory
cd backend

# Clear cache
php artisan cache:clear
php artisan config:clear

# Run migrations (if needed)
php artisan migrate --fresh

# Seed permissions
php artisan db:seed --class=PermissionSeeder

# Seed role permissions  
php artisan db:seed --class=RolePermissionSeeder

# Verify setup
php artisan tinker
```

### Step 2: Verify Files Exist
```bash
# Check all new files created
ls -la app/Services/CallService.php
ls -la tests/Feature/CallTest.php

# Check modified files
ls -la app/Policies/CallPolicy.php
ls -la app/Http/Requests/StoreCallRequest.php
ls -la app/Http/Requests/UpdateCallRequest.php
ls -la app/Http/Controllers/CallController.php
```

---

## Part 1: Code Quality Verification

### ✅ Syntax & Diagnostics

```bash
# Check for syntax errors
php -l app/Services/CallService.php
php -l app/Policies/CallPolicy.php
php -l app/Http/Requests/StoreCallRequest.php
php -l app/Http/Requests/UpdateCallRequest.php
php -l app/Http/Controllers/CallController.php
php -l tests/Feature/CallTest.php
```

**Expected Result**: No errors for all files

### ✅ Static Analysis

```bash
# If using PHPStan or Laravel Pint
php artisan pint --test app/Services/CallService.php
php artisan pint --test app/Policies/CallPolicy.php
```

**Expected Result**: All files pass linting

### ✅ Dependencies

```bash
# Verify required classes exist
php artisan tinker
> use App\Services\CallService;
> use App\Policies\CallPolicy;
> use App\Models\Call;
> use App\Models\CallStatus;
> exit
```

**Expected Result**: No "Class not found" errors

---

## Part 2: Permission & Authorization

### ✅ Permissions Seeded

```bash
php artisan tinker

# Check permissions exist
> \App\Models\Permission::where('name', 'like', 'call.%')->pluck('name');
```

**Expected Result**:
```
array:5 [
  0 => "call.viewAny"
  1 => "call.view"
  2 => "call.create"
  3 => "call.update"
  4 => "call.delete"
]
```

### ✅ Permissions Assigned to Roles

```bash
# Check research_admin has call permissions
> $researchAdmin = \App\Models\Role::where('name', 'research_admin')->first();
> $researchAdmin->permissions->pluck('name')->filter(fn($p) => str_starts_with($p, 'call'));
```

**Expected Result**: All 5 call.* permissions present

### ✅ Super Admin Denied

```bash
# Check super_admin doesn't have call.* permissions
> $superAdmin = \App\Models\Role::where('name', 'super_admin')->first();
> $superAdmin->permissions->pluck('name')->filter(fn($p) => str_starts_with($p, 'call'));
```

**Expected Result**: Empty array (no call.* permissions)

---

## Part 3: Model & Database

### ✅ Call Model

```bash
# Verify model loads correctly
> $call = \App\Models\Call::first();
> $call->status
> $call->university
> exit
```

**Expected Result**: All relationships load without errors

### ✅ Database Integrity

```bash
# Check NOT NULL constraints
> $table = DB::connection()->getDoctrineSchemaManager()->listTableColumns('calls');
> foreach ($table as $col) {
>   if ($col->getName() === 'university_id') {
>     echo "is_null: " . ($col->getNotnull() ? 'NO' : 'YES');
>   }
> }
```

**Expected Result**: `is_null: NO` (NOT NULL enforced)

---

## Part 4: Test Execution

### ✅ Run Full Test Suite

```bash
# Run all Call tests
php artisan test --filter=CallTest
```

**Expected Result**:
```
PASS  Tests\Feature\CallTest
✓ test_research_admin_can_view_calls_in_their_university
✓ test_research_admin_can_create_call
... (all tests pass)

Tests:  13 passed
Time:   X.XXs
```

### ✅ Individual Test Verification

```bash
# Test authorization
php artisan test --filter=test_research_admin_can_view_calls_in_their_university

# Test IDOR prevention
php artisan test --filter=test_cannot_create_call_with_campus_from_different_university

# Test immutability
php artisan test --filter=test_university_id_cannot_be_changed_on_update

# Test deletion restriction
php artisan test --filter=test_cannot_delete_call_with_proposals

# Test public access
php artisan test --filter=test_unauthenticated_can_view_public_published_calls
```

**Expected Result**: All tests pass individually

### ✅ Test Coverage

```bash
# Run with coverage report
php artisan test --filter=CallTest --coverage

# Or generate HTML report
php artisan test --filter=CallTest --coverage --coverage-html=coverage
```

**Expected Result**: Coverage > 85%

---

## Part 5: API Endpoint Verification

### ✅ List Calls (Authenticated)

```bash
# Create test data
php artisan tinker
> $university = \App\Models\University::first();
> $user = \App\Models\User::find(1);
> $user->university_id = $university->id;
> $user->save();
> $call = \App\Models\Call::create([
>   'title' => 'Test Call',
>   'description' => 'Test',
>   'thematic_areas' => 'AI',
>   'deadline' => now()->addDays(30),
>   'university_id' => $university->id,
>   'status_id' => 2,
>   'created_by' => $user->id,
> ]);
> exit
```

**API Test**:
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/calls
```

**Expected Result**: 200 OK, returns call in data

### ✅ List Calls (Unauthenticated)

```bash
# Create public call
php artisan tinker
> $call->is_public = true;
> $call->published_at = now();
> $call->save();
> exit
```

**API Test**:
```bash
curl http://localhost:8000/api/calls
```

**Expected Result**: 200 OK, returns public call

### ✅ Create Call

**API Test**:
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Call",
    "description": "Description",
    "thematic_areas": "AI",
    "deadline": "2026-08-22",
    "university_id": 1
  }' \
  http://localhost:8000/api/calls
```

**Expected Result**: 201 Created

### ✅ Update Call (Immutability Test)

**API Test**:
```bash
curl -X PUT \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "university_id": 2
  }' \
  http://localhost:8000/api/calls/1
```

**Expected Result**: 422 Unprocessable Entity with university_id error

### ✅ Delete Call (With Proposals)

```bash
# Create proposal for call
php artisan tinker
> $proposal = \App\Models\Proposal::create([
>   'call_id' => 1,
>   'user_id' => 1,
>   'title' => 'Test Proposal',
>   'description' => 'Test',
> ]);
> exit
```

**API Test**:
```bash
curl -X DELETE \
  -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/calls/1
```

**Expected Result**: 409 Conflict with message about proposals

---

## Part 6: Security Validation

### ✅ Tenant Isolation

```bash
# Verify research_admin A cannot see university B calls
> $uniA = \App\Models\University::where('code', 'UNI-A')->first();
> $uniB = \App\Models\University::where('code', 'UNI-B')->first();
> $adminA = \App\Models\User::where('university_id', $uniA->id)->role('research_admin')->first();
> $callB = \App\Models\Call::where('university_id', $uniB->id)->first();
> $adminA->can('view', $callB);
```

**Expected Result**: `false` (denied)

### ✅ IDOR Prevention

```bash
# Verify cannot attach foreign campus
php artisan tinker
> $uniA = \App\Models\University::first();
> $uniB = \App\Models\University::where('id', '!=', $uniA->id)->first();
> $campusB = \App\Models\Campus::where('university_id', $uniB->id)->first();
> $call = \App\Models\Call::create([
>   'title' => 'Test',
>   'description' => 'Test',
>   'thematic_areas' => 'AI',
>   'deadline' => now()->addDays(30),
>   'university_id' => $uniA->id,
>   'campus_id' => $campusB->id,
>   'status_id' => 2,
>   'created_by' => 1,
> ]);
```

**Expected Result**: Validation error (campus doesn't belong to university)

### ✅ Super Admin Denied

```bash
# Verify super_admin cannot access tenant calls
> $superAdmin = \App\Models\User::role('super_admin')->first();
> $call = \App\Models\Call::first();
> $superAdmin->can('view', $call);
```

**Expected Result**: `false` (denied)

### ✅ Public Access Fixed

```bash
# Verify private calls not visible
> $call = \App\Models\Call::create([
>   'title' => 'Private',
>   'description' => 'Test',
>   'thematic_areas' => 'AI',
>   'deadline' => now()->addDays(30),
>   'university_id' => 1,
>   'is_public' => false,
>   'published_at' => now(),
>   'status_id' => 2,
>   'created_by' => 1,
> ]);
> $null = null; // Unauthenticated user
> $null can('view', $call);
```

**Expected Result**: `false` (denied for unauthenticated)

---

## Part 7: Downstream Integration

### ✅ Proposal Submission

```bash
# Verify proposal can still be submitted
php artisan tinker
> $call = \App\Models\Call::first();
> $user = \App\Models\User::where('id', '!=', $call->created_by)->first();
> \Illuminate\Support\Facades\Gate::allow(null);
> auth()->setUser($user);
> $user->can('view', $call);
```

**Expected Result**: `true` (proposal can access call)

### ✅ Dashboard Counts

```bash
# Verify dashboard counts only open calls
> $uniA = \App\Models\University::first();
> \App\Models\Call::where('university_id', $uniA->id)->where('status_id', 2)->count();
```

**Expected Result**: Count matches what dashboard should show

### ✅ Public Portal

```bash
# Verify public portal shows correct calls
> \App\Models\Call::where('is_public', true)->where('published_at', '<=', now())->count();
```

**Expected Result**: Only public, published calls counted

---

## Part 8: Performance

### ✅ Query Performance

```bash
php artisan tinker

# Check query performance
> \DB::enableQueryLog();
> \App\Models\Call::visibleTo(\App\Models\User::first())->limit(20)->get();
> \DB::getQueryLog();
```

**Expected Result**: 2-3 queries max (no N+1)

### ✅ Response Time

```bash
# Time API response
time curl http://localhost:8000/api/calls
```

**Expected Result**: < 500ms for list endpoint

---

## Part 9: Documentation

### ✅ Files Exist

```bash
# Check documentation files
ls -la backend/CALL_REFACTORING_COMPLETE.md
ls -la backend/CALL_SECURITY_GUIDE.md
ls -la backend/CALL_MODULE_SUMMARY.md
ls -la backend/CALL_VERIFICATION_CHECKLIST.md
```

**Expected Result**: All files exist

### ✅ Documentation Complete

- [ ] CALL_REFACTORING_COMPLETE.md: Implementation details
- [ ] CALL_SECURITY_GUIDE.md: Developer guide
- [ ] CALL_MODULE_SUMMARY.md: Project summary
- [ ] CALL_VERIFICATION_CHECKLIST.md: This checklist

---

## Final Verification Summary

### Code Quality
- [ ] All files have 0 diagnostics errors
- [ ] All files pass syntax check
- [ ] All files pass linting (if applicable)
- [ ] Dependencies properly imported

### Functionality
- [ ] All 13 tests pass
- [ ] API endpoints respond correctly
- [ ] Validation works as expected
- [ ] Authorization enforced

### Security
- [ ] Tenant isolation verified
- [ ] IDOR prevention confirmed
- [ ] Super admin denied
- [ ] Public access fixed
- [ ] Immutability enforced
- [ ] Deletion restrictions active

### Compatibility
- [ ] API contracts preserved
- [ ] Response structure unchanged
- [ ] Proposal submission works
- [ ] Dashboard compatible
- [ ] Public portal works
- [ ] No breaking changes

### Performance
- [ ] No N+1 queries
- [ ] Response times acceptable
- [ ] Database indexes used
- [ ] No performance regression

### Documentation
- [ ] Implementation documented
- [ ] Security guide created
- [ ] Summary documented
- [ ] This checklist complete

---

## Sign-Off

### Verification By
- **Tester**: [Your Name]
- **Date**: [Date]
- **Result**: [ ] PASS [ ] FAIL

### Issues Found
```
[List any issues found during verification]
```

### Approval
- [ ] Development Lead Approved
- [ ] Security Lead Approved
- [ ] QA Lead Approved
- [ ] Ready for Production

---

## Rollback Plan

If issues are found:

### Immediate Actions
1. Stop deployment
2. Revert code changes: `git revert`
3. Restore previous database: Use backup
4. Verify system restored
5. Document issues found

### Investigation
1. Review issue details
2. Identify root cause
3. Develop fix
4. Create new tests
5. Redeploy

---

## Support Contacts

**For Issues**: Contact development team  
**For Security**: Contact security team  
**For Questions**: Refer to CALL_SECURITY_GUIDE.md

---

**Checklist Version**: 1.0  
**Created**: July 22, 2026  
**Status**: Ready for use
