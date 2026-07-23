# PROPOSAL MODULE - SECURITY QUICK REFERENCE

## 🔒 Critical Security Rules

### 1. NEVER Use `withoutGlobalScopes()`
```php
// ❌ WRONG - Bypasses tenant isolation
$call = Call::withoutGlobalScopes()->find($id);

// ✅ CORRECT - Enforces tenant isolation
$call = Call::query()->find($id);
if (!$call || !$user->can('view', $call)) {
    abort(403);
}
```

### 2. ALWAYS Use API Resources for Responses
```php
// ❌ WRONG - Exposes internal fields
return response()->json($proposal);

// ✅ CORRECT - Filters sensitive data
return response()->json(new ProposalResource($proposal));
return response()->json(ProposalResource::collection($proposals));
```

### 3. NEVER Mass-Assign Protected Fields
```php
// ❌ WRONG - Allows status manipulation
$proposal->update($request->all());
$proposal->update(['status_id' => $newStatus]);

// ✅ CORRECT - Explicit assignment
$proposal->status_id = $newStatus;
$proposal->approved_by = $user->id;
$proposal->approved_at = now();
$proposal->save();
```

### 4. ALWAYS Authorize Before Actions
```php
// ❌ WRONG - No authorization
public function update(Request $request, Proposal $proposal) {
    $proposal->update($request->validated());
}

// ✅ CORRECT - Authorization enforced
public function update(Request $request, Proposal $proposal) {
    $this->authorize('update', $proposal);
    // ... then proceed
}
```

### 5. ALWAYS Validate Resource Ownership
```php
// ❌ WRONG - No ownership check
public function destroy(Proposal $proposal, ProposalInvestigator $investigator) {
    $investigator->delete();
}

// ✅ CORRECT - Verify belongs to proposal
public function destroy(Proposal $proposal, ProposalInvestigator $investigator) {
    $this->authorize('update', $proposal);
    
    if ($investigator->proposal_id !== $proposal->id) {
        abort(404, 'Investigator not found in this proposal.');
    }
    
    $investigator->delete();
}
```

---

## 📋 Proposal Workflow (Correct)

```
1. CREATE (store)
   ├─ Status: DRAFT
   ├─ Notifications: NONE
   └─ User can edit freely

2. SUBMIT (submit)
   ├─ Status: DRAFT → SUBMITTED
   ├─ Validation: Must have investigators
   ├─ Notifications: YES (user, admins, call creator)
   └─ User cannot edit after submit

3. REVIEW (assignReviewers)
   ├─ Status: SUBMITTED → UNDER_REVIEW
   └─ Reviewers notified

4. APPROVE (approve)
   ├─ Status: UNDER_REVIEW → APPROVED
   ├─ Validation: All reviews complete, finance OK, ethics OK
   ├─ Action: Create Project
   └─ Notifications: Submitter notified
```

---

## 🔐 Protected Fields (Never Mass-Assign)

```php
// In Proposal model
protected $guarded = [
    'status_id',              // ⚠️ Only services can change
    'submitted_by',           // ⚠️ Set on creation only
    'submitted_at',           // ⚠️ Set by submit() service
    'approved_by',            // ⚠️ Set by approve() service
    'approved_at',            // ⚠️ Set by approve() service
    'ethics_approval_status_id', // ⚠️ Set by ethics service
    'originality_score',      // ⚠️ Set by plagiarism check
    'plagiarism_report_url'   // ⚠️ Set by plagiarism check
];
```

---

## 🎯 Authorization Checklist

### ProposalController:
- ✅ `index()` - Checks `viewAny` policy
- ✅ `store()` - No explicit check (any authenticated user)
- ✅ `show()` - Checks `view` policy
- ✅ `update()` - Checks `update` policy
- ✅ `destroy()` - Checks `delete` policy
- ✅ `submit()` - Checks `submit` policy
- ✅ `approve()` - Checks `update` policy
- ✅ `reject()` - Checks `update` policy
- ✅ `assignReviewers()` - Checks `assignReviewers` policy

### ProposalFileController:
- ✅ `attach()` - Checks `update` policy + file ownership
- ✅ `detach()` - Checks `update` policy + file belongs to proposal

### ProposalInvestigatorController:
- ✅ `index()` - Checks `view` policy
- ✅ `store()` - Checks `update` policy + duplicate prevention
- ✅ `destroy()` - Checks `update` policy + belongs to proposal

---

## 🧪 Testing Examples

```php
// Test tenant isolation
public function test_cannot_access_other_tenant_proposal()
{
    $myProposal = Proposal::factory()->create(['submitted_by' => $this->user->id]);
    $otherProposal = Proposal::factory()->create(['submitted_by' => $this->otherUser->id]);
    
    $response = $this->actingAs($this->user)->getJson("/api/proposals/{$otherProposal->id}");
    $response->assertStatus(403);
}

// Test mass assignment protection
public function test_cannot_mass_assign_status()
{
    $response = $this->actingAs($this->user)->postJson('/api/proposals', [
        'title' => 'Test',
        'status_id' => 5, // Try to set to "approved"
    ]);
    
    $proposal = Proposal::latest()->first();
    $this->assertEquals(1, $proposal->status_id); // Should still be draft
}

// Test data leakage prevention
public function test_resource_filters_sensitive_data()
{
    $proposal = Proposal::factory()->create();
    $response = $this->actingAs($this->admin)->getJson("/api/proposals/{$proposal->id}");
    
    $response->assertJsonStructure([
        'id', 'title',
        'status' => ['id', 'name'], // Structured, not raw ID
        'submitted_by' => ['id', 'name'], // Structured, not raw ID
    ]);
}
```

---

## ⚡ Common Mistakes to Avoid

### Mistake 1: Returning Raw Models
```php
// ❌ EXPOSES INTERNAL DATA
return response()->json(Proposal::find($id));

// ✅ FILTERED
return response()->json(new ProposalResource(Proposal::find($id)));
```

### Mistake 2: Skipping Authorization
```php
// ❌ UNAUTHORIZED ACCESS POSSIBLE
Route::get('/proposals/{proposal}', function(Proposal $proposal) {
    return response()->json($proposal);
});

// ✅ AUTHORIZED
Route::get('/proposals/{proposal}', [ProposalController::class, 'show']);
// (Controller has: $this->authorize('view', $proposal))
```

### Mistake 3: Using Mass Assignment for Status
```php
// ❌ BYPASSES WORKFLOW
$proposal->update(['status_id' => $approvedStatusId]);

// ✅ USES SERVICE
$this->proposalService->approve($proposal, $user);
```

### Mistake 4: Not Validating Child Resources
```php
// ❌ ALLOWS CROSS-PROPOSAL MANIPULATION
public function destroy(Proposal $proposal, ProposalInvestigator $investigator) {
    $investigator->delete(); // Could delete investigator from different proposal!
}

// ✅ VALIDATES BELONGS TO PROPOSAL
public function destroy(Proposal $proposal, ProposalInvestigator $investigator) {
    if ($investigator->proposal_id !== $proposal->id) {
        abort(404);
    }
    $investigator->delete();
}
```

---

## 📊 Status Transitions (Valid)

```
DRAFT ──submit()──> SUBMITTED
                        │
                    assignReviewers()
                        │
                        ▼
                   UNDER_REVIEW
                        │
            ┌───────────┼───────────┐
            │                       │
        approve()              reject()
            │                       │
            ▼                       ▼
        APPROVED                REJECTED
            │
    (Project Created)
```

**Invalid Transitions:**
- ❌ DRAFT → APPROVED (must go through review)
- ❌ SUBMITTED → APPROVED (must go through review)
- ❌ APPROVED → DRAFT (no rollback)

---

## 🚨 Red Flags in Code Reviews

Watch for these patterns:
- 🚨 `withoutGlobalScopes()` anywhere
- 🚨 `return response()->json($model)` without Resource
- 🚨 `$model->update($request->all())`
- 🚨 Missing `$this->authorize()` calls
- 🚨 `protected $fillable = ['status_id', ...]`
- 🚨 Child resource actions without parent validation

---

## ✅ Security Checklist for New Features

Before adding new Proposal features:
- [ ] Use ProposalResource for all responses
- [ ] Add authorization check (`$this->authorize()`)
- [ ] Use explicit assignment for protected fields
- [ ] Validate child resources belong to parent
- [ ] Never use `withoutGlobalScopes()`
- [ ] Write tests for tenant isolation
- [ ] Write tests for authorization
- [ ] Update this documentation

---

## 📞 Need Help?

- **Security Issue?** Review: `PROPOSAL_MODULE_AUDIT_REPORT.md`
- **Testing?** Check: `tests/Feature/ProposalTest.php`
- **Policies?** See: `app/Policies/ProposalPolicy.php`
- **Workflow?** Read: This document (Status Transitions section)

---

**Last Updated:** January 23, 2026  
**Version:** 1.0 (Post-Security-Audit)
