# Call Module - Security Guide for Developers

**Last Updated**: July 22, 2026  
**Version**: 1.0  
**Status**: Production Ready

---

## Quick Start

### For Admins: Creating a Call

```php
// Use policy authorization
$this->authorize('create', Call::class);

// Use validated request (automatic validation)
$call = Call::create([
    'title' => 'Research Call',
    'description' => 'Description',
    'thematic_areas' => 'AI',
    'deadline' => now()->addDays(30),
    'university_id' => auth()->user()->university_id, // Forced to user's university
]);

// Result: Call created with automatic permissions check, validation, and tenant isolation
```

### For Users: Viewing Calls

```php
// Unauthenticated: Only public, published calls
GET /api/calls
// Returns: Calls where is_public=true AND published_at <= now()

// Authenticated: Tenant-scoped calls
Sanctum::actingAs($admin);
GET /api/calls
// Returns: Calls visible to user's tenant (via visibleTo scope)
```

### For Developers: Extending Calls

```php
// DO: Use CallService for business logic
$canDelete = app(CallService::class)->canDelete($call);
if (!$canDelete) {
    return response()->json(['error' => 'Has proposals'], 409);
}

// DON'T: Access database directly
// Wrong: if ($call->proposals()->count() > 0) { ... }

// DO: Use policy for authorization
$this->authorize('delete', $call);

// DON'T: Check roles in controller
// Wrong: if (!auth()->user()->hasRole('research_admin')) { ... }
```

---

## Security Architecture

### Layer 1: Authorization (Policy)

**File**: `app/Policies/CallPolicy.php`

Decides **who can do what** based on permissions and tenant ownership.

```php
public function view(?User $user, Call $call): bool
{
    // Unauthenticated: check is_public + published_at
    if (!$user) {
        return $call->is_public && $call->published_at <= now();
    }
    
    // Authenticated: check permission + tenant ownership
    return $user->hasPermission('call.view') && $user->university_id === $call->university_id;
}
```

**Key Points**:
- ✅ Permission-based (call.viewAny, call.view, etc.)
- ✅ Tenant-aware (university_id checked)
- ✅ Super admin explicitly denied
- ✅ No hardcoded roles

### Layer 2: Validation (Request)

**Files**: `app/Http/Requests/StoreCallRequest.php`, `UpdateCallRequest.php`

Ensures **request data is valid and secure**.

```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        // Tenant validation
        if ($universityId != auth()->user()->university_id) {
            $validator->errors()->add('university_id', 'Not your university');
        }
        
        // Hierarchy validation
        $campus = Campus::find($campusId);
        if ($campus->university_id != $universityId) {
            $validator->errors()->add('campus_id', 'Campus not in this university');
        }
    });
}
```

**Key Points**:
- ✅ Tenant-aware validation
- ✅ Hierarchy consistency checked
- ✅ IDOR prevented server-side
- ✅ User-friendly error messages

### Layer 3: Business Logic (Service)

**File**: `app/Services/CallService.php`

Implements **business rules** (deletion restrictions, status transitions, etc.).

```php
public function canDelete(Call $call): bool
{
    return $call->proposals()->count() === 0;
}

public function canEdit(Call $call, array $fields): array
{
    if ($call->status?->name === 'open') {
        $restrictedFields = ['deadline', 'thematic_areas'];
        return [
            'allowed' => !array_intersect($restrictedFields, array_keys($fields)),
            'restricted_fields' => $restrictedFields,
        ];
    }
    return ['allowed' => true, 'restricted_fields' => []];
}
```

**Key Points**:
- ✅ Business rules centralized
- ✅ Reusable across codebase
- ✅ Testable in isolation
- ✅ Clear business intent

### Layer 4: Control (Controller)

**File**: `app/Http/Controllers/CallController.php`

Coordinates **authorization, validation, and business logic**.

```php
public function destroy(Call $call): JsonResponse
{
    // Step 1: Authorize
    $this->authorize('delete', $call);
    
    // Step 2: Check business rules
    if (!$this->callService->canDelete($call)) {
        return response()->json([...], 409); // 409 Conflict
    }
    
    // Step 3: Execute
    $call->delete();
    
    return response()->json(['message' => 'Call deleted']);
}
```

**Key Points**:
- ✅ Orchestrates security layers
- ✅ Returns proper HTTP status codes
- ✅ Business logic delegated to service
- ✅ Clear intent

---

## Security Patterns

### Pattern 1: Tenant Isolation

**Goal**: Ensure users cannot access other tenants' data

**Implementation**:

```php
// In Request Validation
if ($universityId != auth()->user()->university_id) {
    $validator->errors()->add('university_id', 'Not your university');
}

// In Policy
private function sameUniversity(User $user, Call $call): bool
{
    return $user->university_id === $call->university_id;
}

// In Service
public function getVisibleCalls(User $user, Builder $query): Builder
{
    return $query->where('university_id', $user->university_id);
}
```

**Verification**:
```bash
# Test: Research admin cannot access other university
php artisan test --filter=test_research_admin_cannot_view_calls_from_other_university
```

### Pattern 2: IDOR Prevention

**Goal**: Prevent users from modifying resources they don't own

**Implementation**:

```php
// DON'T: Trust user input
// Wrong:
$campus = Campus::find($request->campus_id);

// DO: Validate ownership
// Correct:
$campus = Campus::find($campusId);
if ($campus->university_id != auth()->user()->university_id) {
    abort(403);
}
```

**Verification**:
```bash
# Test: Cannot create call in other university
php artisan test --filter=test_cannot_create_call_with_campus_from_different_university
```

### Pattern 3: Immutability Protection

**Goal**: Prevent users from changing ownership

**Implementation**:

```php
// In UpdateCallRequest
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        if ($this->has('university_id')) {
            $validator->errors()->add('university_id', 'Cannot change after creation');
        }
    });
}

// In Controller (defensive)
public function update(UpdateCallRequest $request, Call $call)
{
    $validated = $request->validated();
    unset($validated['university_id']); // Extra safety
    $call->update($validated);
}
```

**Verification**:
```bash
# Test: university_id cannot be changed
php artisan test --filter=test_university_id_cannot_be_changed_on_update
```

### Pattern 4: Public Access Control

**Goal**: Only show public calls to unauthenticated users

**Implementation**:

```php
// In Policy
public function view(?User $user, Call $call): bool
{
    if (!$user) {
        // Check is_public AND published_at
        return $call->is_public && $call->published_at <= now();
    }
    // Authenticated: normal authorization
    return $user->hasPermission('call.view') && ...;
}

// In Controller
if (!$user) {
    $query->where('is_public', true)
          ->where('published_at', '<=', now());
}
```

**Verification**:
```bash
# Test: Unauthenticated see only public published calls
php artisan test --filter=test_unauthenticated_can_view_public_published_calls
```

### Pattern 5: Business Rule Enforcement

**Goal**: Enforce status transitions and deletion restrictions

**Implementation**:

```php
// In Service
public function canDelete(Call $call): bool
{
    return $call->proposals()->count() === 0;
}

public function validateStatusTransition(Call $call, int $newStatusId): bool
{
    $transitions = [
        'draft' => ['open'],
        'open' => ['closed'],
        'closed' => [],
    ];
    return in_array(...);
}

// In Controller
if (!$this->callService->canDelete($call)) {
    return response()->json([...], 409); // 409 Conflict
}
```

**Verification**:
```bash
# Test: Cannot delete call with proposals
php artisan test --filter=test_cannot_delete_call_with_proposals
```

---

## Common Mistakes

### ❌ Mistake 1: Checking Roles Instead of Permissions

```php
// WRONG
if (!auth()->user()->hasRole('research_admin')) {
    abort(403);
}

// RIGHT
if (!auth()->user()->hasPermission('call.create')) {
    abort(403);
}
```

**Why**: Permissions are dynamic and can be changed per institution. Roles are global and cannot.

### ❌ Mistake 2: Magic Hierarchy Population

```php
// WRONG
$data['campus_id'] = $department->faculty->campus_id;
$data['university_id'] = $department->faculty->campus->university_id;

// RIGHT
// Trust validated request - validation already checked hierarchy
```

**Why**: autoFillHierarchy() enabled IDOR. Validation is safer.

### ❌ Mistake 3: Allowing Ownership Changes

```php
// WRONG
$call->update($validated); // Could include university_id

// RIGHT
unset($validated['university_id']);
$call->update($validated);
```

**Why**: Ownership must be immutable for security.

### ❌ Mistake 4: Business Logic in Controller

```php
// WRONG
public function destroy(Call $call)
{
    if ($call->proposals()->count() > 0) {
        abort(403);
    }
    $call->delete();
}

// RIGHT
public function destroy(Call $call)
{
    if (!$this->callService->canDelete($call)) {
        return response()->json([...], 409);
    }
    $call->delete();
}
```

**Why**: Services are testable, reusable, and maintainable.

### ❌ Mistake 5: Missing Tenant Checks

```php
// WRONG
$call = Call::find($id); // Could be from other university

// RIGHT
// Authorization in policy + validation in request
$this->authorize('view', $call);
```

**Why**: Policy and validation provide defense in depth.

---

## Testing Checklist

When adding new features to Call module, verify:

### Authorization
- [ ] Unauthenticated cannot create/update/delete
- [ ] Super admin cannot access tenant resources
- [ ] Users without permission are denied
- [ ] Cross-tenant access is denied

### Validation
- [ ] Hierarchy is validated (campus→university, etc.)
- [ ] Tenant ownership is enforced
- [ ] Invalid data returns 422 (Unprocessable Entity)
- [ ] Error messages are user-friendly

### Business Rules
- [ ] Cannot delete call with proposals (409 Conflict)
- [ ] Status transitions are validated
- [ ] university_id is immutable
- [ ] Edit restrictions based on status

### Data Integrity
- [ ] Soft delete works
- [ ] Relationships are maintained
- [ ] Cascade deletes don't occur unexpectedly
- [ ] Foreign keys are respected

### API Compatibility
- [ ] Response format unchanged
- [ ] Pagination works
- [ ] Relationships loaded
- [ ] HTTP status codes correct

### Public Access
- [ ] Unauthenticated see only public calls
- [ ] published_at is respected
- [ ] is_public flag is checked
- [ ] Portal continues working

---

## Running Tests

### All Call Tests
```bash
php artisan test --filter=CallTest
```

### Specific Test
```bash
php artisan test --filter=test_cannot_delete_call_with_proposals
```

### With Detailed Output
```bash
php artisan test --filter=CallTest --verbose
```

### Coverage Report
```bash
php artisan test --filter=CallTest --coverage
```

---

## Troubleshooting

### Issue: "Unauthenticated cannot view call"

**Cause**: is_public or published_at not set

**Fix**:
```php
$call = Call::create([
    ...,
    'is_public' => true,
    'published_at' => now(),
]);
```

### Issue: "403 Forbidden on create"

**Cause**: Permission not assigned or role wrong

**Fix**:
```bash
# Verify permissions are seeded
php artisan db:seed --class=PermissionSeeder

# Verify permissions are assigned to role
php artisan db:seed --class=RolePermissionSeeder
```

### Issue: "Cannot create call in campus"

**Cause**: Campus doesn't belong to university

**Fix**:
```php
$campus = Campus::where('university_id', $universityId)->first();
$call->create([
    'university_id' => $universityId,
    'campus_id' => $campus->id, // Must belong to university
]);
```

### Issue: "Cannot delete call"

**Cause**: Call has proposals

**Fix**:
```php
// Delete proposals first (if allowed)
$call->proposals()->delete();

// Or return 409 and inform user
return response()->json([
    'error' => 'Call has proposals'
], 409);
```

---

## Performance Tips

### 1. Use visibleTo() Scope for Lists
```php
// FAST (uses visibleTo scope with indexes)
Call::visibleTo(auth()->user())->paginate(20);

// SLOW (no scope, full table scan)
Call::paginate(20)->filter(fn($c) => ...);
```

### 2. Eager Load Relationships
```php
// FAST (2 queries total)
Call::with('status', 'university', 'proposals')->get();

// SLOW (N+1 queries)
foreach ($calls as $call) {
    echo $call->status->name; // Query per call
}
```

### 3. Cache Policy Checks
```php
// Policy checks are cached automatically
// No need to manually cache permission checks
```

---

## Resources

- **Implementation Plan**: `CALL_IMPLEMENTATION_PLAN.md`
- **Analysis**: `CALL_MODULE_ANALYSIS.md`
- **Business Rules**: `CALL_BUSINESS_RULES_VALIDATION.md`
- **Test Suite**: `tests/Feature/CallTest.php`

---

## Questions?

For questions about Call module security or implementation, refer to:
1. This guide (patterns and examples)
2. Test file (working examples)
3. Source code comments (inline documentation)
4. Implementation plan (design decisions)

---

**Last Updated**: July 22, 2026  
**Maintained By**: Kiro AI  
**Status**: Production Ready
