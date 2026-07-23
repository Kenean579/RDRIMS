# Call Module - Comprehensive Root Cause Analysis

## Executive Summary

**Module**: Research Call Management  
**Analysis Date**: July 22, 2026  
**Complexity**: VERY HIGH (Multi-tenant, hierarchical scoping, public/private visibility, critical for Proposal workflow)  
**Current State**: SIGNIFICANT SECURITY & ARCHITECTURAL ISSUES  
**Severity**: **CRITICAL** - Multiple high-risk vulnerabilities affecting tenant isolation, IDOR, and authorization

---

## Module Purpose & Business Context

### What is a Call?
A **Research Call** (or "Call for Proposals") is a research funding opportunity published by institutions to solicit research proposals from researchers. Calls are the **entry point** for the entire proposal submission workflow.

### Critical Relationships
```
Call → Proposals → Reviews → Projects → Outputs
  ↓
University/Campus/Faculty/Department/Research Center (Hierarchical Scoping)
```

### Visibility Modes
1. **Public Calls**: Visible on public portal, open to external researchers
2. **Private Calls**: Internal only, scoped to specific institutional levels

### Hierarchy Support
Calls can be scoped at multiple levels:
- **Global**: No institutional affiliation (super_admin only)
- **University-level**: Available to entire university
- **Campus-level**: Scoped to specific campus
- **Faculty-level**: Scoped to specific faculty
- **Department-level**: Scoped to specific department
- **Research Center-level**: Scoped to specific research center

---

## Architecture Understanding

### Database Schema Analysis

**Table**: `calls`

**Core Fields**:
- `id` (PK)
- `title`, `description`, `thematic_areas`
- `deadline` (indexed)
- `created_by` (FK → users)
- `status_id` (FK → call_statuses)

**Hierarchical Scoping** (all nullable, all indexed):
- `university_id` (FK → universities)
- `campus_id` (FK → campuses)
- `faculty_id` (FK → faculties)
- `department_id` (FK → departments)
- `research_center_id` (FK → research_centers)

**Visibility Control**:
- `is_public` (boolean, default true)
- `is_featured` (boolean, default false)
- `published_at`, `opens_at`, `closes_at` (timestamps)

**Performance Optimization**:
- Composite indexes on `(institution_id, status_id)` for dashboard queries

**Key Observations**:
✅ Good: Composite indexes for performance
✅ Good: Soft deletes support
⚠️ Risk: All hierarchy fields nullable without validation
⚠️ Risk: No uniqueness constraints on title
⚠️ Risk: `thematic_areas` as TEXT without structure

---

## Current Implementation Analysis

### Model (`Call.php`)

**Traits Used**:
- `HasFactory` - Standard Laravel
- `SoftDeletes` - Standard Laravel
- `HierarchicalScope` - Custom trait (need to understand)
- `BelongsToUniversity` - Custom trait

**Relationships** (✅ Well-defined):
- `createdBy()` → User
- `status()` → CallStatus
- `academicYear()` → AcademicYear
- `guidelineFile()` → File
- `university()`, `campus()`, `faculty()`, `department()`, `researchCenter()` → Hierarchy models
- `proposals()` → HasMany Proposal

**Query Scopes**:
1. `scopePublished()` - Calls with `published_at` not null
2. `scopeOpen()` - Calls currently open (between `opens_at` and `closes_at`)
3. `scopePublic()` - Calls with `is_public = true`
4. **`scopeVisibleTo(User $user)`** - ⚠️ **CRITICAL SECURITY LOGIC**

### ⚠️ ROOT CAUSE #1: Complex Role-Based Visibility Logic in Model

**Problem**: The `scopeVisibleTo()` method contains 100+ lines of hardcoded role checks.

**Code Smell**:
```php
public function scopeVisibleTo(Builder $query, User $user): Builder
{
    if ($user->hasRole('super_admin')) {
        return $query; // ❌ Super admin bypass
    }

    return $query->where(function (Builder $q) use ($user) {
        $q->whereNull('university_id'); // ❌ Global calls

        if ($user->hasRole('research_admin')) {
            $q->orWhere('university_id', $user->resolvedUniversityId());
        }

        if ($user->hasRole('campus_admin') && $user->campus_id) {
            $q->orWhere('campus_id', $user->campus_id);
        }

        // ... 80 more lines of role checks
    });
}
```

**Issues Identified**:
1. **Business logic in Model** - Violates separation of concerns
2. **Hardcoded roles** - Not using dynamic permissions
3. **Complex OR conditions** - Hard to test and maintain
4. **No tenant isolation enforcement** - Relies entirely on this scope
5. **Performance risk** - Complex WHERE clauses on every query
6. **Maintainability nightmare** - Adding new roles requires model changes

**Impact**: CRITICAL - This is the primary tenant isolation mechanism, and it's fragile.

---

### Controller (`CallController.php`)

### ⚠️ ROOT CAUSE #2: Inconsistent Authorization Pattern

**Problem**: Mix of policy-based and custom validation logic.

**Analysis**:

#### `index()` Method:
```php
public function index(Request $request): JsonResponse
{
    $user = $request->user();

    $calls = Call::with(...)
        ->when($user, fn ($query) => $query->visibleTo($user)) // ❌ Relies on model scope
        ->paginate(20);

    return response()->json($calls);
}
```

**Issues**:
1. ❌ **No `authorize()` call** - Skips policy entirely
2. ❌ **Relies on `visibleTo()` scope** - Hidden authorization
3. ❌ **Public endpoint without explicit auth check** - Security risk
4. ⚠️ **Complex filtering logic** - 20+ lines of `when()` chains

#### `store()` Method:
```php
public function store(StoreCallRequest $request): JsonResponse
{
    $user = $request->user();
    $this->authorize('create', Call::class); // ✅ Policy check
    $this->validateScopeForRole($request, $user); // ❌ Custom validation

    $validated = $request->validated();
    $validated = $this->autoFillHierarchy($validated, $user); // ❌ Auto-magic

    // ❌ Hardcoded default values
    if (empty($validated['status_id'])) {
        $defaultStatus = CallStatus::where('name', 'open')->first();
        $validated['status_id'] = $defaultStatus ? $defaultStatus->id : 2;
    }

    $call = Call::create([
        ...$validated,
        'created_by' => $user->id,
    ]);

    return response()->json($call, 201);
}
```

**Issues**:
1. ✅ **Policy check** - Good start
2. ❌ **`validateScopeForRole()`** - Duplicates policy logic in controller
3. ❌ **`autoFillHierarchy()`** - Magic behavior, bypasses validation
4. ❌ **Hardcoded defaults** - Should be in database or request
5. ❌ **No hierarchy consistency validation** - Can create invalid combinations

### ⚠️ ROOT CAUSE #3: Dangerous `autoFillHierarchy()` Method

**Problem**: Automatically populates parent hierarchy fields, bypassing validation.

```php
private function autoFillHierarchy(array $data, $user): array
{
    if (!empty($data['department_id'])) {
        $department = Department::with('faculty.campus')->find($data['department_id']);
        if ($department) {
            $data['faculty_id'] = $department->faculty_id;
            $data['campus_id'] = $department->faculty->campus_id ?? null;
            $data['university_id'] = $department->faculty->campus->university_id ?? null;
        }
    }
    // ... similar for faculty_id, campus_id

    if (empty($data['university_id']) && $user && !$user->hasRole('super_admin')) {
        $data['university_id'] = $user->resolvedUniversityId(); // ❌ Auto-assign
    }

    return $data;
}
```

**Issues**:
1. ❌ **IDOR vulnerability** - Doesn't verify user owns the department/faculty/campus
2. ❌ **Bypasses validation** - Adds fields after validation passes
3. ❌ **Hidden behavior** - API clients don't know this happens
4. ❌ **N+1 queries** - Loads relationships unnecessarily
5. ❌ **Inconsistent with other modules** - Campus/Faculty/Department don't do this

**Attack Scenario**:
```json
POST /api/calls
{
  "title": "Malicious Call",
  "department_id": 999  // Department from University B
}
```
Result: Controller auto-fills `university_id` from department 999, allowing cross-tenant call creation!

### ⚠️ ROOT CAUSE #4: Weak `validateScopeForRole()` Method

**Problem**: Redundant validation logic that duplicates policy checks.

```php
private function validateScopeForRole($request, $user): void
{
    if ($user->hasRole('super_admin')) {
        return; // ❌ Super admin bypass
    }

    $userUniversity = $user->university_id ?: $user->department?->faculty?->campus?->university_id;
    $userCampus = $user->campus_id ?: $user->department?->faculty?->campus_id;
    $userFaculty = $user->faculty_id ?: $user->department?->faculty_id;
    $userDept = $user->department_id;

    if ($user->hasRole('research_admin')) {
        if ($request->filled('university_id') && $request->input('university_id') != $userUniversity) {
            abort(403, 'You can only scope calls to your own university.');
        }
    }

    // ... 30 more lines of hardcoded role checks
}
```

**Issues**:
1. ❌ **Hardcoded roles** - Not using permissions
2. ❌ **Duplicates policy logic** - Should be in policy
3. ❌ **Weak validation** - Only checks if fields are "filled", not if they're valid
4. ❌ **No hierarchy consistency check** - Can create invalid combinations
5. ❌ **Called AFTER validation** - Too late to reject invalid data

---

### Policy (`CallPolicy.php`)

### ⚠️ ROOT CAUSE #5: Hardcoded Role-Based Authorization

**Problem**: Policy uses hardcoded roles instead of dynamic permissions.

**Analysis**:

#### `viewAny()` Method:
```php
public function viewAny(?User $user): bool
{
    return true; // ❌ Always allows, even without authentication!
}
```

**Issue**: PUBLIC SECURITY RISK - Any anonymous user can call index endpoint.

#### `view()` Method:
```php
public function view(?User $user, Call $call): bool
{
    if (!$user) {
        return true; // ❌ Unauthenticated access allowed!
    }

    if ($user->hasRole('super_admin')) {
        return true;
    }

    if ($call->created_by && (int) $call->created_by === (int) $user->id) {
        return true; // ❌ Creator bypass without tenant check
    }

    $userUniId = $user->resolvedUniversityId();
    return $call->university_id === null || (int) $call->university_id === (int) $userUniId;
}
```

**Issues**:
1. ❌ **Allows unauthenticated access** - Should check `is_public` flag
2. ❌ **Creator bypass** - No tenant verification
3. ❌ **Weak tenant check** - `university_id === null` allows global access
4. ❌ **No permission check** - Just role checks

#### `create()` Method:
```php
public function create(User $user): bool
{
    return $user->hasAnyRole([
        'super_admin',
        'research_admin',
        'director',
        'campus_admin',
        'faculty_admin',
        'department_head',
    ]);
}
```

**Issue**: ❌ **Hardcoded roles** - Should use `call.create` permission.

#### `update()` Method:
```php
public function update(User $user, Call $call): bool
{
    if ($user->hasRole('super_admin')) {
        return true;
    }

    $userUniId = $user->resolvedUniversityId();
    if ($call->university_id !== null && (int)$call->university_id !== (int)$userUniId) {
        return false;
    }

    if ($user->hasRole('research_admin')) {
        return true; // ❌ Can update ANY call in their university
    }

    if ($user->hasRole('campus_admin')) {
        $userCampus = $user->campus_id ?: $user->department?->faculty?->campus_id;
        return $userCampus && (int)$call->campus_id === (int)$userCampus;
    }

    // ... more hardcoded role checks
}
```

**Issues**:
1. ❌ **Hardcoded roles** - Not using permissions
2. ❌ **Weak hierarchy verification** - Only checks one level
3. ❌ **Research admin can update ANY university call** - Too broad
4. ❌ **No ownership verification** - Should check `created_by`
5. ❌ **Complex nested conditions** - Hard to test

---

### Request Validation

### ⚠️ ROOT CAUSE #6: No Tenant-Aware Validation

**Problem**: Validation only checks if IDs exist, not if user owns them.

**`StoreCallRequest.php`**:
```php
'university_id' => [
    'nullable',
    Rule::exists('universities', 'id'), // ❌ Only checks existence
],

'campus_id' => [
    'nullable',
    Rule::exists('campuses', 'id'), // ❌ No university check
],
```

**Issues**:
1. ❌ **No tenant ownership validation** - User can specify ANY university_id
2. ❌ **No hierarchy consistency validation** - Campus may not belong to university
3. ❌ **No IDOR protection** - Validation happens before authorization
4. ❌ **Missing immutability protection** - No constraints on updates

**Attack Scenario**:
```json
POST /api/calls
{
  "title": "IDOR Attack",
  "university_id": 999,  // University B
  "campus_id": 888,      // Campus from University A
  "faculty_id": 777      // Faculty from Campus B
}
```
Result: Validation passes! Invalid hierarchy created.

### ⚠️ ROOT CAUSE #7: No Immutability Protection

**Problem**: `UpdateCallRequest` allows changing hierarchy fields.

```php
'university_id' => [
    'sometimes',
    'nullable',
    Rule::exists('universities', 'id'), // ❌ Can be changed!
],
```

**Issue**: Calls can be moved between universities/campuses/faculties after creation, enabling IDOR attacks.

**Attack Scenario**:
1. Create call in University A
2. Update call, change `university_id` to University B
3. Call now belongs to University B without validation!

---

## Routes Analysis

**Public Endpoints** (No authentication required):
```php
Route::get('calls', [CallController::class, 'index']);
Route::get('calls/{call}', [CallController::class, 'show']);
```

**Protected Endpoints** (Authentication required):
```php
Route::apiResource('calls', CallController::class)->except(['index', 'show']);
```

**Issues**:
1. ✅ **Public endpoints for portal** - Correct design
2. ❌ **No middleware on protected routes** - Missing role middleware like other modules
3. ❌ **Inconsistent with other modules** - Campus/Faculty/Department use role middleware

---

## Permission System Analysis

### ⚠️ ROOT CAUSE #8: No Granular Permissions Defined

**Finding**: Searched `PermissionSeeder.php` for `call.*` permissions - **NONE FOUND**.

**Issues**:
1. ❌ **No permissions seeded** - Module relies entirely on hardcoded roles
2. ❌ **Inconsistent with refactored modules** - Campus/Faculty/Department have granular permissions
3. ❌ **Cannot use dynamic authorization** - Must check roles directly
4. ❌ **No fine-grained access control** - All-or-nothing per role

**Required Permissions** (Missing):
```php
'call.viewAny'   - View list of calls
'call.view'      - View individual call
'call.create'    - Create new call
'call.update'    - Update call
'call.delete'    - Delete call
'call.publish'   - Publish call (optional, for workflow)
'call.feature'   - Feature call on portal (optional)
```

---

## Test Coverage Analysis

### ⚠️ ROOT CAUSE #9: ZERO Test Coverage

**Finding**: Searched for `CallTest` - **NO TESTS FOUND**.

**Critical Gap**: The most complex module in the system has ZERO automated tests.

**Missing Test Coverage**:
- ❌ Authorization (viewAny, view, create, update, delete)
- ❌ Tenant isolation (cross-university access)
- ❌ Hierarchy validation (invalid combinations)
- ❌ IDOR prevention (foreign institution assignment)
- ❌ Public/private visibility
- ❌ Immutability (hierarchy changes)
- ❌ `visibleTo()` scope logic
- ❌ Proposal relationship integrity
- ❌ API contract validation

**Risk**: HIGH - Changes will break functionality without detection.

---

## Security Vulnerabilities Summary

### CRITICAL Vulnerabilities

#### 1. **IDOR: Cross-Tenant Call Creation/Update**
**Severity**: CRITICAL  
**Attack Vector**: User from University A can create/update calls in University B by specifying `university_id`  
**Root Cause**: No tenant-aware validation in requests, `autoFillHierarchy()` bypasses checks  
**Affected Operations**: CREATE, UPDATE  

#### 2. **IDOR: Invalid Hierarchy Combinations**
**Severity**: CRITICAL  
**Attack Vector**: Create call with campus from Uni A, faculty from Uni B, department from Uni C  
**Root Cause**: No server-side hierarchy consistency validation  
**Affected Operations**: CREATE, UPDATE  

#### 3. **Weak Tenant Isolation in `visibleTo()`**
**Severity**: HIGH  
**Attack Vector**: Complex OR conditions may leak calls across tenants  
**Root Cause**: Fragile hardcoded logic in model scope  
**Affected Operations**: INDEX, SHOW  

#### 4. **Unauthenticated Access to Non-Public Calls**
**Severity**: HIGH  
**Attack Vector**: Anonymous users can view private calls if policy returns true  
**Root Cause**: `viewAny()` always returns true, `view()` allows unauthenticated if no user  
**Affected Operations**: INDEX, SHOW  

#### 5. **Hierarchy Immutability Not Enforced**
**Severity**: HIGH  
**Attack Vector**: Move call between universities after creation  
**Root Cause**: No immutability validation in `UpdateCallRequest`  
**Affected Operations**: UPDATE  

#### 6. **Creator Bypass Without Tenant Check**
**Severity**: MEDIUM  
**Attack Vector**: User creates call in Uni A, moves to Uni B (via admin), can still access it  
**Root Cause**: Policy checks `created_by` before `university_id`  
**Affected Operations**: VIEW, UPDATE, DELETE  

---

### HIGH-RISK Code Patterns

1. **Hardcoded Roles Instead of Permissions**
   - Makes authorization inflexible
   - Cannot use role-based access control (RBAC) properly
   - Violates single responsibility principle

2. **Business Logic in Model Scope**
   - `visibleTo()` contains authorization logic
   - Hard to test in isolation
   - Violates separation of concerns

3. **Auto-Magic Behavior in Controller**
   - `autoFillHierarchy()` changes data after validation
   - `validateScopeForRole()` duplicates policy logic
   - Hidden from API documentation

4. **Simple `exists()` Rules Without Ownership**
   - Allows specifying any valid ID regardless of ownership
   - No IDOR protection at validation layer

5. **Nullable Hierarchy Without Validation**
   - All hierarchy fields nullable
   - No constraint to prevent invalid combinations
   - Database allows inconsistent states

---

## Architectural Issues

### 1. **Separation of Concerns Violations**

**Issue**: Authorization, filtering, validation scattered across multiple layers.

**Responsibilities Mixing**:
- Model: Contains authorization logic (`visibleTo()`)
- Controller: Contains validation logic (`validateScopeForRole()`)
- Controller: Contains business logic (`autoFillHierarchy()`)
- Policy: Incomplete authorization logic
- Request: Insufficient validation

**Impact**: Hard to maintain, test, and reason about.

### 2. **Inconsistency with Refactored Modules**

**Call Module** vs **Campus/Faculty/Department Modules**:

| Aspect | Call | Campus/Faculty/Dept |
|--------|------|---------------------|
| Permissions | ❌ None | ✅ Granular (*.viewAny, *.create, etc.) |
| Authorization | ❌ Hardcoded roles | ✅ Policy-based with permissions |
| Validation | ❌ Simple exists() | ✅ Tenant-aware with hierarchy checks |
| Immutability | ❌ Not enforced | ✅ Enforced in request validation |
| Tests | ❌ Zero | ✅ 15-36 comprehensive tests |
| IDOR Protection | ❌ Weak | ✅ Strong (server-side validation) |

**Impact**: Inconsistent security posture across modules creates confusion and risk.

### 3. **Missing Service Layer**

**Issue**: No `CallService` or `TenantService` to encapsulate complex business logic.

**Current State**:
- Complex filtering logic in controller `index()` method
- `autoFillHierarchy()` as private controller method
- `validateScopeForRole()` as private controller method
- `visibleTo()` scope in model

**Ideal State**:
```php
// Service handles complexity
class CallService {
    public function getVisibleCalls(User $user, array $filters): Collection
    public function createCall(array $data, User $user): Call
    public function validateHierarchy(array $data): bool
}
```

**Impact**: Controllers become bloated, logic not reusable.

### 4. **Performance Concerns**

**Issue**: `visibleTo()` scope generates complex SQL queries.

**Example Query**:
```sql
SELECT * FROM calls
WHERE (
  university_id IS NULL
  OR (university_id = 1)
  OR (campus_id = 2)
  OR (faculty_id = 3)
  OR (department_id = 4)
  OR research_center_id IN (5, 6, 7)
)
ORDER BY deadline DESC
LIMIT 20;
```

**Concerns**:
- OR conditions prevent index usage
- Multiple subqueries for different roles
- N+1 problem with `researchCenters()` query in director check
- No query result caching

**Impact**: Slow dashboard and listing pages for users with multiple roles.

---

## Integration Analysis

### Relationship with Proposal Module

**Flow**: Call → Proposal (one-to-many)

**Critical Requirements**:
1. Proposals must belong to valid, active calls
2. Proposals inherit tenant context from call
3. Call deletion/status changes affect proposals

**Current State**:
- ✅ `calls.id` foreign key in proposals table
- ✅ `proposals()` relationship in Call model
- ⚠️ No validation that proposal university matches call university
- ⚠️ Call deletion cascade behavior unclear

**Risk**: If call tenant isolation is weak, proposal tenant isolation is compromised.

### Dashboard Integration

**Query Pattern**:
```php
Call::where('university_id', $user->university_id)
    ->where('status_id', $statusId)
    ->count();
```

**Concerns**:
- Relies on correct `university_id` assignment
- If calls have incorrect `university_id`, dashboard counts are wrong
- Composite indexes help performance but don't fix data integrity

### Public Portal Integration

**Requirement**: Anonymous users must see only public, published calls.

**Current Implementation**:
```php
// Policy allows unauthenticated
if (!$user) {
    return true;
}
```

**Issues**:
- ❌ Doesn't check `is_public` flag
- ❌ Doesn't check `published_at` date
- ❌ Could expose private calls to public

**Correct Approach**:
```php
if (!$user) {
    return $call->is_public && $call->published_at <= now();
}
```

---

## Root Causes Ranked by Priority

### Priority 1: CRITICAL Security Fixes

1. **No Tenant-Aware Validation** → IDOR vulnerabilities
2. **`autoFillHierarchy()` Bypasses Validation** → Cross-tenant access
3. **No Hierarchy Consistency Validation** → Invalid data states
4. **Hardcoded Roles Instead of Permissions** → Inflexible authorization
5. **No Immutability Protection** → Post-creation IDOR

### Priority 2: HIGH Security Fixes

6. **Weak Public/Private Access Control** → Unauth access to private calls
7. **Creator Bypass Without Tenant Check** → Privilege escalation
8. **Complex `visibleTo()` Logic in Model** → Hidden authorization bugs
9. **`validateScopeForRole()` Duplicates Policy** → Inconsistent enforcement

### Priority 3: Architectural Improvements

10. **No Permission System** → Cannot use dynamic RBAC
11. **No Service Layer** → Business logic scattered
12. **Business Logic in Model** → Separation of concerns violation
13. **No Test Coverage** → Changes break functionality

### Priority 4: Performance & Maintainability

14. **Complex OR Queries** → Performance degradation
15. **N+1 Query in `visibleTo()`** → Slow listing
16. **Inconsistency with Other Modules** → Confusing codebase
17. **Missing API Documentation** → Hidden behaviors

---

## Security Requirements

### Mandatory Requirements

1. **Tenant Isolation**
   - User from University A MUST NOT access calls from University B
   - Enforced at: Policy, Validation, Query layers

2. **Hierarchy Validation**
   - Campus MUST belong to specified university
   - Faculty MUST belong to specified campus
   - Department MUST belong to specified faculty
   - Research Center MUST belong to specified university

3. **IDOR Prevention**
   - User MUST NOT specify foreign institution IDs
   - Validation MUST verify ownership before accepting hierarchy fields

4. **Immutability**
   - `university_id` MUST NOT change after creation
   - `campus_id`, `faculty_id`, `department_id`, `research_center_id` MUST NOT change

5. **Public/Private Visibility**
   - Anonymous users MUST see only `is_public = true` AND `published_at <= now()` calls
   - Authenticated users MUST see calls within their tenant scope + public calls

6. **Permission-Based Authorization**
   - MUST use dynamic permissions (`call.viewAny`, `call.create`, etc.)
   - MUST NOT hardcode role checks in policies

7. **Ownership Verification**
   - Research Admin MUST NOT update calls they didn't create (unless explicitly allowed)
   - Lower-level admins (campus/faculty/dept) MUST only manage calls within their scope

---

## Refactoring Strategy

### Phase 1: Analysis & Planning (✅ COMPLETE)
- Understand module architecture
- Identify root causes
- Document security requirements
- Define refactoring approach

### Phase 2: Implementation (NEXT)
1. Create permissions (PermissionSeeder)
2. Rewrite CallPolicy with dynamic permissions
3. Add tenant-aware validation (StoreCallRequest, UpdateCallRequest)
4. Remove `autoFillHierarchy()` and `validateScopeForRole()` from controller
5. Refactor `visibleTo()` scope or move to service
6. Add immutability protection
7. Fix public/private access logic
8. Clean up controller CRUD methods

### Phase 3: Testing (REQUIRED)
- Create comprehensive CallTest suite (30+ tests)
- Test authorization at all levels
- Test tenant isolation
- Test hierarchy validation
- Test IDOR prevention
- Test immutability
- Test public/private visibility
- Test integration with Proposal module

### Phase 4: Verification (REQUIRED)
- Run all tests (Call + existing modules)
- Check diagnostics (0 errors)
- Verify API compatibility
- Document changes

---

## Conclusion

The Call module is the **most critical and most vulnerable** module in RDRIMS. It serves as the entry point for the entire research proposal workflow, yet it has:

- ❌ Zero test coverage
- ❌ No permission system
- ❌ Weak tenant isolation
- ❌ Multiple IDOR vulnerabilities
- ❌ No hierarchy validation
- ❌ Hardcoded authorization logic
- ❌ Complex, unmaintainable code
- ❌ Inconsistent with other refactored modules

**Recommendation**: **CRITICAL PRIORITY REFACTORING REQUIRED**

This module MUST be refactored to enterprise standards before production deployment. The security risks are too high to ignore.

**Estimated Effort**: 2-3 phases (Analysis ✅, Implementation, Testing)  
**Complexity**: VERY HIGH (Multi-tenant, hierarchical, public/private, integrated with proposals)  
**Priority**: **IMMEDIATE**

---

**Analysis Completed**: July 22, 2026  
**Status**: Ready for Phase 2 Implementation
