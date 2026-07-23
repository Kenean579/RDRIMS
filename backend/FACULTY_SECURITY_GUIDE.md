# Faculty Module Security & Usage Guide

## Quick Reference for Developers

### Required Permissions

| Action | Permission | Who Has It |
|--------|-----------|------------|
| List faculties | `faculty.viewAny` | research_admin, campus_admin, faculty_admin |
| View faculty details | `faculty.view` | research_admin, campus_admin, faculty_admin |
| Create faculty | `faculty.create` | research_admin, campus_admin |
| Update faculty | `faculty.update` | research_admin, campus_admin |
| Delete faculty | `faculty.delete` | research_admin, campus_admin |

### API Endpoints

```
GET    /api/faculties              # List faculties (tenant-filtered)
POST   /api/faculties              # Create faculty
GET    /api/faculties/{faculty}    # View faculty details
PUT    /api/faculties/{faculty}    # Update faculty
DELETE /api/faculties/{faculty}    # Delete faculty
```

### Tenant Isolation Rules

1. **Research Admin** can only manage faculties within their own university
2. **Campus Admin** can manage faculties within their campus
3. **Faculty Admin** can only view (read-only)
4. **Super Admin** is explicitly DENIED (tenant resources only)

### Security Checks

#### ✅ Safe Operations
```php
// Create faculty in user's own campus
POST /api/faculties
{
    "name": "Engineering Faculty",
    "code": "ENG",
    "campus_id": 123  // Must belong to user's university
}

// Update faculty name
PUT /api/faculties/1
{
    "name": "Updated Name"
}
```

#### ❌ Prevented Operations
```php
// ❌ Create faculty in another university's campus
POST /api/faculties
{
    "campus_id": 999  // Different university
}
// Result: 422 Validation Error

// ❌ Change faculty's campus
PUT /api/faculties/1
{
    "campus_id": 456  // Trying to move to different campus
}
// Result: 422 Validation Error

// ❌ Super admin accessing faculty
GET /api/faculties  // As super_admin
// Result: 403 Forbidden
```

### Code Examples

#### Policy Check Example
```php
// In controller
public function update(UpdateFacultyRequest $request, Faculty $faculty)
{
    // Policy automatically checks:
    // 1. User has 'faculty.update' permission
    // 2. Faculty belongs to user's university
    // 3. User is NOT super_admin
    $this->authorize('update', $faculty);
    
    $faculty->update($request->validated());
    return response()->json($faculty);
}
```

#### Tenant Filtering Example
```php
// In controller index method
public function index()
{
    $user = auth()->user();
    
    // Only returns faculties from user's university
    $faculties = Faculty::with(['campus', 'logoFile'])
        ->whereHas('campus', function ($q) use ($user) {
            $q->where('university_id', $user->university_id);
        })
        ->get();
    
    return response()->json($faculties);
}
```

#### Request Validation Example
```php
// In StoreFacultyRequest
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        $user = $this->user();
        $campusId = $this->input('campus_id');

        if ($campusId && $user) {
            $campus = Campus::find($campusId);

            // Server-side tenant check
            if (!$campus || $campus->university_id !== $user->university_id) {
                $validator->errors()->add(
                    'campus_id',
                    'The selected campus does not belong to your university.'
                );
            }
        }
    });
}
```

### Common Pitfalls

#### ❌ DON'T: Trust client-supplied tenant fields
```php
// BAD - Never trust user input for tenant fields
$faculty = Faculty::create([
    'name' => $request->name,
    'campus_id' => $request->campus_id,  // ❌ No validation!
]);
```

#### ✅ DO: Validate tenant ownership server-side
```php
// GOOD - Validate in request class
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        $campus = Campus::find($this->input('campus_id'));
        if (!$campus || $campus->university_id !== $this->user()->university_id) {
            $validator->errors()->add('campus_id', 'Invalid campus.');
        }
    });
}
```

#### ❌ DON'T: Allow changing immutable fields
```php
// BAD - Allows moving faculty to different campus
public function update(Request $request, Faculty $faculty)
{
    $faculty->update($request->all());  // ❌ campus_id can change!
}
```

#### ✅ DO: Explicitly prevent immutable field changes
```php
// GOOD - Remove campus_id from update data
public function update(UpdateFacultyRequest $request, Faculty $faculty)
{
    $data = $request->validated();
    unset($data['campus_id']);  // ✅ Prevent campus change
    $faculty->update($data);
}
```

### Testing Examples

```php
/** @test */
public function research_admin_cannot_access_other_university_faculty(): void
{
    Sanctum::actingAs($this->researchAdminA);

    // Try to access Faculty B (from University B)
    $response = $this->getJson("/api/faculties/{$this->facultyB->id}");

    $response->assertForbidden();  // ✅ Blocked by policy
}

/** @test */
public function cannot_create_faculty_in_other_university_campus(): void
{
    Sanctum::actingAs($this->researchAdminA);

    // Try to create faculty in Campus B (from University B)
    $response = $this->postJson('/api/faculties', [
        'name' => 'Malicious Faculty',
        'code' => 'MAL',
        'campus_id' => $this->campusB->id,  // Different university
    ]);

    $response->assertUnprocessable();  // ✅ Blocked by validation
    $response->assertJsonValidationErrors(['campus_id']);
}
```

### Debugging Authorization Issues

#### Check User Permissions
```php
$user = auth()->user();

// Check if user has permission
$hasPermission = $user->hasPermission('faculty.viewAny');

// Get all effective permissions
$permissions = $user->getEffectivePermissionIds();

// Check university
$universityId = $user->university_id;
```

#### Check Faculty Ownership
```php
$faculty = Faculty::find($id);

// Check if faculty belongs to user's university
$belongsToUser = $faculty->campus?->university_id === auth()->user()->university_id;

// Or use helper method
$belongsToUser = $faculty->belongsToUniversity(auth()->user()->university_id);
```

### Migration Notes

If upgrading from old Faculty implementation:

1. **Permissions:** Run `php artisan db:seed --class=PermissionSeeder`
2. **Role Mappings:** Run `php artisan db:seed --class=RolePermissionSeeder`
3. **Tests:** Run `php artisan test --filter FacultyTest`
4. **Cache:** Clear permission cache: `php artisan cache:clear`

### Security Checklist

Before deploying Faculty-related changes:

- [ ] Authorization check in controller method?
- [ ] Policy verifies tenant ownership?
- [ ] Request validates campus_id belongs to user's university?
- [ ] Immutable fields (campus_id) protected from changes?
- [ ] Super admin explicitly denied?
- [ ] Test covers cross-tenant access attempts?
- [ ] Error messages don't leak tenant information?

### Support

For questions or issues:
- See full documentation: `backend/FACULTY_MODULE_REFACTORING.md`
- Run tests: `php artisan test --filter FacultyTest`
- Check diagnostics: Review policy and request validation logic
