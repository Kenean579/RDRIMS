# Call Module - Final Implementation Plan

**Status**: Ready for Implementation  
**Date**: July 22, 2026  
**Approach**: Security fixes while preserving ALL existing functionality

---

## Executive Summary

**Goal**: Transform Call module to enterprise-grade security following Campus/Faculty/Department/Research Center patterns.

**Constraints**:
- ✅ NO UI changes
- ✅ NO API contract changes
- ✅ NO database schema changes
- ✅ NO breaking changes to Proposal/Dashboard/Public Portal
- ✅ PRESERVE all existing routes and endpoints
- ✅ PRESERVE public access behavior

**Security Fixes**:
1. Replace hardcoded roles with dynamic permissions
2. Add tenant-aware validation
3. Add hierarchy consistency validation
4. Add immutability protection
5. Fix IDOR vulnerabilities
6. Fix public/private access logic
7. Move business logic to appropriate layers

---

## Part 1: Permission System

### Step 1.1: Add Permissions to PermissionSeeder

**File**: `database/seeders/PermissionSeeder.php`

**Add**:
```php
'call.viewAny' => 'View list of calls',
'call.view' => 'View individual call',
'call.create' => 'Create calls',
'call.update' => 'Update calls',
'call.delete' => 'Delete calls',
```

### Step 1.2: Assign Permissions to Roles

**File**: `database/seeders/RolePermissionSeeder.php`

**Assign to roles**:
- `research_admin`: all call.* permissions
- `campus_admin`: all call.* permissions
- `faculty_admin`: all call.* permissions
- `department_head`: all call.* permissions
- `director`: all call.* permissions
- `super_admin`: EXCLUDE from tenant resources

### Step 1.3: Update AuthServiceProvider Gate

**File**: `app/Providers/AuthServiceProvider.php`

**Verify**: `call.*` abilities denied for super_admin in `Gate::before()`

---

## Part 2: Policy Refactoring

### Step 2.1: Rewrite CallPolicy with Permissions

**File**: `app/Policies/CallPolicy.php`

**Pattern** (follows Campus/Faculty/Department):

```php
public function viewAny(?User $user): bool
{
    // Allow unauthenticated for public portal
    if (!$user) {
        return true; // Public access preserved
    }
    
    if ($user->hasRole('super_admin')) {
        return false;
    }
    
    return $user->hasPermission('call.viewAny');
}

public function view(?User $user, Call $call): bool
{
    // Allow unauthenticated for public calls
    if (!$user) {
        return $call->is_public && $call->published_at && $call->published_at <= now();
    }
    
    if ($user->hasRole('super_admin')) {
        return false;
    }
    
    // Check permission + tenant ownership
    return $user->hasPermission('call.view') 
        && $this->sameUniversity($user, $call);
}

public function create(User $user): bool
{
    if ($user->hasRole('super_admin')) {
        return false;
    }
    
    return $user->hasPermission('call.create');
}

public function update(User $user, Call $call): bool
{
    if ($user->hasRole('super_admin')) {
        return false;
    }
    
    return $this->sameUniversity($user, $call)
        && $user->hasPermission('call.update');
}

public function delete(User $user, Call $call): bool
{
    if ($user->hasRole('super_admin')) {
        return false;
    }
    
    return $this->sameUniversity($user, $call)
        && $user->hasPermission('call.delete');
}

private function sameUniversity(User $user, Call $call): bool
{
    return $user->university_id !== null
        && $call->university_id !== null
        && $user->university_id === $call->university_id;
}
```

**Key Changes**:
- ✅ Permission-based instead of role-based
- ✅ Public access preserved for portal
- ✅ Tenant isolation enforced
- ✅ Super admin excluded

---

## Part 3: Request Validation

### Step 3.1: Refactor StoreCallRequest

**File**: `app/Http/Requests/StoreCallRequest.php`

**Changes**:

1. **Fix university_id validation** (required per DB schema):
```php
'university_id' => [
    'required', // ← Changed from nullable
    Rule::exists('universities', 'id'),
],
```

2. **Add withValidator() for tenant-aware validation**:
```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        $user = $this->user();
        $universityId = $this->input('university_id');
        $campusId = $this->input('campus_id');
        $facultyId = $this->input('faculty_id');
        $departmentId = $this->input('department_id');
        $researchCenterId = $this->input('research_center_id');

        // Validate university belongs to user
        if ($universityId && $user->university_id && $universityId != $user->university_id) {
            $validator->errors()->add(
                'university_id',
                'You can only create calls within your own university.'
            );
            return;
        }

        // Validate campus belongs to university
        if ($campusId) {
            $campus = Campus::find($campusId);
            if (!$campus || $campus->university_id != $universityId) {
                $validator->errors()->add(
                    'campus_id',
                    'The selected campus must belong to the selected university.'
                );
                return;
            }
        }

        // Validate faculty belongs to campus
        if ($facultyId) {
            if (!$campusId) {
                $validator->errors()->add(
                    'faculty_id',
                    'Faculty can only be selected when campus is specified.'
                );
                return;
            }

            $faculty = Faculty::find($facultyId);
            if (!$faculty || $faculty->campus_id != $campusId) {
                $validator->errors()->add(
                    'faculty_id',
                    'The selected faculty must belong to the selected campus.'
                );
                return;
            }
        }

        // Validate department belongs to faculty
        if ($departmentId) {
            if (!$facultyId) {
                $validator->errors()->add(
                    'department_id',
                    'Department can only be selected when faculty is specified.'
                );
                return;
            }

            $department = Department::find($departmentId);
            if (!$department || $department->faculty_id != $facultyId) {
                $validator->errors()->add(
                    'department_id',
                    'The selected department must belong to the selected faculty.'
                );
                return;
            }
        }

        // Validate research center belongs to university
        if ($researchCenterId) {
            $center = ResearchCenter::find($researchCenterId);
            if (!$center || $center->parent_university_id != $universityId) {
                $validator->errors()->add(
                    'research_center_id',
                    'The selected research center must belong to the selected university.'
                );
            }
        }
    });
}
```

**Key Features**:
- ✅ Tenant-aware validation
- ✅ Hierarchy consistency checks
- ✅ IDOR prevention
- ✅ Server-side enforcement

### Step 3.2: Refactor UpdateCallRequest

**File**: `app/Http/Requests/UpdateCallRequest.php`

**Changes**:

1. **Add immutability protection**:
```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        // Prevent changing university (immutability)
        if ($this->has('university_id')) {
            $validator->errors()->add(
                'university_id',
                'The university cannot be changed after creation.'
            );
        }

        // Allow changing other hierarchy fields if still valid
        // but validate they belong to the university
        $call = $this->route('call');
        $campusId = $this->input('campus_id');
        $facultyId = $this->input('faculty_id');
        $departmentId = $this->input('department_id');
        $researchCenterId = $this->input('research_center_id');

        if ($campusId) {
            $campus = Campus::find($campusId);
            if (!$campus || $campus->university_id != $call->university_id) {
                $validator->errors()->add(
                    'campus_id',
                    'The selected campus must belong to the call university.'
                );
            }
        }

        if ($facultyId) {
            $faculty = Faculty::find($facultyId);
            $campus = $campusId ? Campus::find($campusId) : $call->campus;
            if (!$faculty || !$campus || $faculty->campus_id != $campus->id) {
                $validator->errors()->add(
                    'faculty_id',
                    'The selected faculty must belong to the call campus.'
                );
            }
        }

        if ($departmentId) {
            $department = Department::find($departmentId);
            $faculty = $facultyId ? Faculty::find($facultyId) : $call->faculty;
            if (!$department || !$faculty || $department->faculty_id != $faculty->id) {
                $validator->errors()->add(
                    'department_id',
                    'The selected department must belong to the call faculty.'
                );
            }
        }

        if ($researchCenterId) {
            $center = ResearchCenter::find($researchCenterId);
            if (!$center || $center->parent_university_id != $call->university_id) {
                $validator->errors()->add(
                    'research_center_id',
                    'The selected research center must belong to the call university.'
                );
            }
        }
    });
}
```

**Key Features**:
- ✅ university_id immutable
- ✅ Other hierarchy fields validated against university
- ✅ IDOR prevention on updates

---

## Part 4: Controller Refactoring

### Step 4.1: Refactor CallController

**File**: `app/Http/Controllers/CallController.php`

**Changes**:

1. **Remove autoFillHierarchy()** - Security risk
2. **Remove validateScopeForRole()** - Duplicate of policy
3. **Simplify store() method**:

```php
public function store(StoreCallRequest $request): JsonResponse
{
    $this->authorize('create', Call::class);

    $user = $request->user();
    $validated = $request->validated();

    // Force user's university if not provided (but validation ensures it's correct)
    if (empty($validated['university_id'])) {
        $validated['university_id'] = $user->university_id;
    }

    // Set default status if not provided
    if (empty($validated['status_id'])) {
        $defaultStatus = CallStatus::where('name', 'open')->first();
        $validated['status_id'] = $defaultStatus ? $defaultStatus->id : 2;
    }

    // Set default thematic_areas if empty
    if (empty($validated['thematic_areas'])) {
        $validated['thematic_areas'] = 'General';
    }

    $call = Call::create([
        ...$validated,
        'created_by' => $user->id,
    ]);

    return response()->json($call->load('status', 'academicYear', 'guidelineFile'), 201);
}
```

4. **Add immutability to update() method**:

```php
public function update(UpdateCallRequest $request, Call $call): JsonResponse
{
    $this->authorize('update', $call);

    $validated = $request->validated();

    // Enforce immutability: university_id cannot change
    unset($validated['university_id']);

    $call->update($validated);

    return response()->json(
        $call->fresh()->load('status', 'academicYear', 'guidelineFile')
    );
}
```

5. **Simplify index() method**:

```php
public function index(Request $request): JsonResponse
{
    $user = $request->user();

    // Authorization handled by policy
    if ($user) {
        $this->authorize('viewAny', Call::class);
    }

    $query = Call::with(
        'status',
        'academicYear',
        'createdBy.profileImage',
        'guidelineFile',
        'proposals'
    )->withCount('proposals');

    // Apply filters
    $query->when($request->filled('status'), fn($q) =>
        $q->whereHas('status', fn($s) => $s->where('name', $request->input('status')))
    );

    $query->when($request->filled('search'), fn($q) =>
        $q->where(function ($searchQuery) use ($request) {
            $searchQuery->where('title', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('thematic_areas', 'LIKE', '%' . $request->input('search') . '%');
        })
    );

    // Tenant filtering
    if ($user) {
        // Authenticated: use visibleTo scope
        $query->visibleTo($user);
    } else {
        // Unauthenticated: only public, published calls
        $query->where('is_public', true)
              ->whereNotNull('published_at')
              ->where('published_at', '<=', now());
    }

    return response()->json($query->orderBy('deadline', 'desc')->paginate(20));
}
```

6. **Update show() method**:

```php
public function show(Call $call): JsonResponse
{
    $this->authorize('view', $call);

    return response()->json(
        $call->load('status', 'academicYear', 'guidelineFile', 'proposals')
    );
}
```

**Key Changes**:
- ✅ Removed security risks (autoFillHierarchy)
- ✅ Policy-based authorization
- ✅ Immutability enforced
- ✅ Public access preserved
- ✅ Simplified logic

---

## Part 5: Model Scope Refactoring

### Step 5.1: Document visibleTo() Scope

**File**: `app/Models/Call.php`

**Decision**: KEEP existing `visibleTo()` scope for backward compatibility

**Reason**:
- Dashboard uses `Call::visibleTo($user)`
- Changing signature would break downstream
- Complex logic works (just not maintainable)

**Add documentation**:
```php
/**
 * Scope calls visible to the authenticated user.
 * 
 * NOTE: This scope contains complex role-based filtering logic.
 * For new features, prefer policy-based authorization instead.
 * 
 * @deprecated Consider refactoring to service layer in future
 */
public function scopeVisibleTo(Builder $query, User $user): Builder
{
    // Existing implementation unchanged
}
```

**Alternative** (if time permits):
- Extract to `CallService::getVisibleCalls(User $user, Builder $query)`
- Keep scope as wrapper for compatibility

---

## Part 6: Testing Strategy

### Step 6.1: Create Comprehensive Test Suite

**File**: `tests/Feature/CallTest.php` (NEW)

**Test Categories** (35+ tests):

1. **Authorization Tests** (7 tests):
   - Research admin can view calls in their university
   - Research admin cannot view calls from other university
   - Super admin cannot view tenant calls
   - Research admin can create call
   - Super admin cannot create call
   - Research admin can update own call
   - Research admin cannot update other university call

2. **Public Access Tests** (4 tests):
   - Unauthenticated can view public published calls
   - Unauthenticated cannot view private calls
   - Unauthenticated cannot view unpublished calls
   - Public endpoint returns correct data

3. **Hierarchy Validation Tests** (6 tests):
   - Cannot create call with campus from different university
   - Cannot create call with faculty from different campus
   - Cannot create call with department from different faculty
   - Cannot create call with research center from different university
   - Valid hierarchy creation succeeds
   - Missing hierarchy parent validation works

4. **IDOR Prevention Tests** (5 tests):
   - Research admin cannot create call in other university
   - Research admin cannot attach foreign campus
   - Research admin cannot attach foreign faculty
   - Research admin cannot attach foreign department
   - Research admin cannot attach foreign research center

5. **Immutability Tests** (3 tests):
   - university_id cannot be changed on update
   - Other hierarchy fields can be updated if valid
   - Update with invalid hierarchy rejected

6. **Status & Lifecycle Tests** (4 tests):
   - Default status is 'open'
   - Status can be changed by authorized user
   - Call with proposals can be viewed
   - Call with proposals can be soft deleted

7. **Proposal Integration Tests** (3 tests):
   - Proposal can be submitted to valid call
   - Proposal cannot be submitted after deadline
   - Proposal validates call access

8. **Dashboard Integration Tests** (2 tests):
   - Dashboard counts only open calls
   - Dashboard respects tenant isolation

9. **Miscellaneous Tests** (3 tests):
   - Soft delete works
   - Call code must be unique (if applicable)
   - Unauthenticated cannot create/update/delete

**Test Pattern**:
```php
public function test_research_admin_can_view_calls_in_their_university(): void
{
    Sanctum::actingAs($this->researchAdminA);

    $response = $this->getJson('/api/calls');

    $response->assertOk();
    $data = $response->json('data');
    $this->assertGreaterThan(0, count($data));
    $response->assertJsonFragment(['code' => 'CALL-A']);
    $response->assertJsonMissing(['code' => 'CALL-B']);
}
```

---

## Part 7: Business Rule Decisions (Requires Approval)

### Decision 1: Call Edit Restrictions

**Current**: No restrictions on editing calls with proposals

**Options**:
A. **No restrictions** (current behavior) - Allow full editing
B. **Partial restrictions** - Prevent changing hierarchy, allow other fields
C. **Full lockdown** - Prevent all edits after first proposal

**Recommendation**: **Option B** (Partial)
- Prevent university_id changes (implemented)
- Allow other fields (status, dates, description)
- Warn user if proposals exist (UI feature, not backend)

**Impact**: Low - preserves flexibility while enforcing security

---

### Decision 2: Call Deletion with Proposals

**Current**: Soft delete allowed, proposals orphaned (call_id kept)

**Options**:
A. **Allow deletion** (current) - Proposals keep call_id, call soft deleted
B. **Prevent deletion** - Block delete if proposals exist
C. **Cascade soft delete** - Mark proposals as deleted too

**Recommendation**: **Option A** (Keep current)
- FK constraint: ON DELETE SET NULL (on force delete)
- Soft delete doesn't trigger FK (call_id remains)
- Maintains historical data integrity
- Proposals can still be viewed with soft-deleted call

**Impact**: None - preserves current behavior

---

### Decision 3: Status Transition Rules

**Current**: No enforced transitions

**Options**:
A. **No enforcement** (current) - Allow any status change
B. **Linear transitions** - draft → open → closed only
C. **Flexible transitions** - Allow reopening (closed → open)

**Recommendation**: **Option A** (No enforcement)
- Simple model (3 statuses)
- Admins have flexibility
- No business requirement for enforcement found

**Impact**: None - preserves current behavior

---

### Decision 4: Public Call Visibility

**Current**: Broken - unauthenticated see all calls

**Fix**: Check `is_public` AND `published_at` for unauthenticated users

**Implementation**:
- Policy `viewAny()`: Allow unauthenticated
- Policy `view()`: Check `is_public` + `published_at` for unauthenticated
- Controller `index()`: Filter by `is_public` + `published_at` for unauthenticated

**Impact**: **BREAKING** for incorrectly configured calls
- Calls with `is_public=false` hidden from portal (correct behavior)
- Calls with `published_at=null` hidden from portal (correct behavior)
- Fix: Ensure existing calls have correct flags

---

### Decision 5: university_id Required Validation

**Current**: Validation allows NULL (mismatch with DB)

**Fix**: Enforce `required` in StoreCallRequest

**Implementation**:
- Change validation: `'university_id' => 'required|exists:universities,id'`
- Controller ensures user's university used if not provided
- Aligns with DB schema (NOT NULL column)

**Impact**: None - current code already defaults to user's university

---

## Part 8: Implementation Sequence

### Phase 1: Permissions & Policy (Low Risk)
1. Add permissions to PermissionSeeder
2. Run `php artisan db:seed --class=PermissionSeeder`
3. Update RolePermissionSeeder
4. Run `php artisan db:seed --class=RolePermissionSeeder`
5. Verify AuthServiceProvider Gate configuration
6. Rewrite CallPolicy with permissions
7. Run policy-only tests

### Phase 2: Validation (Medium Risk)
1. Update StoreCallRequest with tenant-aware validation
2. Update UpdateCallRequest with immutability protection
3. Test validation rules independently
4. Verify error messages are user-friendly

### Phase 3: Controller (Higher Risk)
1. Remove `autoFillHierarchy()` method
2. Remove `validateScopeForRole()` method
3. Update `store()` method
4. Update `update()` method with immutability
5. Update `index()` method with public access fix
6. Update `show()` method (minimal changes)
7. Update `destroy()` method (minimal changes)

### Phase 4: Testing (Critical)
1. Create CallTest.php with 35+ tests
2. Run tests: `php artisan test --filter=CallTest`
3. Verify all tests pass
4. Run integration tests with Proposal/Dashboard
5. Manual testing of public portal

### Phase 5: Verification (Critical)
1. Check diagnostics: `0 errors`
2. Test API contracts unchanged
3. Test public portal access
4. Test authenticated access
5. Test proposal submission
6. Test dashboard counts
7. Document any breaking changes

---

## Part 9: Risk Mitigation

### High-Risk Changes

1. **Removing autoFillHierarchy()**
   - Risk: Breaks hierarchy auto-population
   - Mitigation: Validation ensures correct hierarchy
   - Test: Create call with department_id only → should fail (correct)

2. **Adding university_id required**
   - Risk: Breaks API if clients send null
   - Mitigation: Controller defaults to user's university
   - Test: Send request without university_id → should succeed

3. **Public access filtering**
   - Risk: Hides calls from portal
   - Mitigation: Ensure existing calls have correct flags
   - Test: Unauthenticated → see only public, published calls

### Medium-Risk Changes

1. **Policy permission checks**
   - Risk: Denies access if permissions not seeded
   - Mitigation: Run seeders before testing
   - Test: Check user has permissions

2. **Immutability protection**
   - Risk: Breaks admin workflow
   - Mitigation: university_id immutable, others flexible
   - Test: Cannot change university, can change campus

### Low-Risk Changes

1. **Removing validateScopeForRole()**
   - Risk: None (duplicate logic in policy/validation)
   - Mitigation: Validation + policy cover all cases

2. **Adding tests**
   - Risk: None (only additions)
   - Mitigation: Tests validate behavior

---

## Part 10: Compatibility Verification

### API Contract Checklist

✅ **Routes unchanged**:
- `GET /api/calls` (public)
- `GET /api/calls/{id}` (public)
- `POST /api/calls` (protected)
- `PUT /api/calls/{id}` (protected)
- `DELETE /api/calls/{id}` (protected)

✅ **Request structure unchanged**:
- Same field names
- Same validation (stricter but compatible)
- Same optional fields

✅ **Response structure unchanged**:
- Same field names
- Same relationships loaded
- Same pagination format

✅ **Downstream compatibility**:
- `Call::visibleTo($user)` method signature unchanged
- `call_id` FK in proposals unchanged
- Soft delete behavior unchanged
- Status relationship unchanged

---

## Part 11: Post-Implementation Checklist

### Before Code Merge

- [ ] All 35+ tests passing
- [ ] 0 diagnostics errors
- [ ] Manual testing completed:
  - [ ] Public portal (unauthenticated)
  - [ ] Authenticated user (research admin)
  - [ ] Create call (valid hierarchy)
  - [ ] Update call (immutability enforced)
  - [ ] Delete call (soft delete works)
  - [ ] Submit proposal (validates call access)
  - [ ] Dashboard counts (correct numbers)
- [ ] API documentation updated
- [ ] Business rule decisions documented
- [ ] Breaking changes documented (if any)

### Documentation Required

1. **CALL_REFACTORING_COMPLETE.md** - Implementation summary
2. **CALL_SECURITY_GUIDE.md** - Developer guide
3. **CALL_REFACTORING_SUMMARY.md** - Executive summary
4. Update **CALL_MODULE_ANALYSIS.md** - Mark issues as resolved

---

## Conclusion

**Implementation Status**: ✅ **APPROVED FOR EXECUTION**

This plan preserves ALL existing functionality while fixing CRITICAL security vulnerabilities:
- ✅ Tenant isolation enforced
- ✅ IDOR vulnerabilities eliminated
- ✅ Dynamic permissions implemented
- ✅ Hierarchy validation added
- ✅ Immutability protected
- ✅ Public access fixed
- ✅ Code maintainability improved

**Estimated Effort**: 1 implementation phase  
**Risk Level**: MEDIUM (many changes, but well-planned)  
**Test Coverage**: 35+ comprehensive tests  
**Breaking Changes**: MINIMAL (only security fixes)

**Ready to proceed with implementation.**

---

**Plan Completed**: July 22, 2026  
**Reviewer**: Kiro AI  
**Status**: ✅ AWAITING USER APPROVAL FOR BUSINESS RULE DECISIONS
