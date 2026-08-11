# 08 — Resource Testing Strategy

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically Testing Foundation).
- The approved Phase 03 Resource Foundation document.
- The approved Phase 03 Taxonomy Implementation document.
- The approved Phase 03 Calculators Architecture document.
- The approved Phase 03 Laboratory Tools Architecture document.
- The approved Phase 03 Search Implementation document.
- The approved Phase 03 Resource Admin CRUD Patterns document.
- The approved Phase 03 Resource Public Page Patterns document.

## Defines

This document defines:

- The testing philosophy for LabGato resources.
- The unit testing strategy for calculation and tool logic.
- The feature testing strategy for admin CRUD operations.
- The feature testing strategy for public resource pages.
- The integration testing strategy for search and taxonomy.
- The accessibility testing strategy for resource interfaces.
- The performance testing strategy for resource queries.
- The test data management strategy.
- The continuous integration and regression rules.

## Affects

This document affects:

- All future resource unit tests.
- All future resource feature tests.
- All future resource integration tests.
- The Definition of Done for all resource vertical slices.
- The CI/CD pipeline configuration.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the mandatory testing baseline for all resource implementations.
- Ensures mathematical accuracy for calculators and tools.
- Prevents security regressions in admin CRUD operations.
- Guarantees public page accessibility and SEO compliance.
- Provides the testing criteria for marking a resource vertical slice as complete.

---

# 1. Purpose

This document establishes the Resource Testing Strategy for LabGato.

Resources are the core value of LabGato. A bug in a calculator, a security flaw in admin CRUD, or an inaccessible public page can undermine the platform's professional credibility.

This document defines the testing requirements that every resource implementation must satisfy before it can be considered complete.

---

# 2. Testing Philosophy for Resources

## 2.1 Accuracy First

For Calculators and Tools, mathematical accuracy is the highest priority.

Every calculation engine must be tested against known expected results.

## 2.2 Security by Default

Admin CRUD operations must be tested for authorization, validation, and privilege escalation prevention.

## 2.3 Accessibility Always

Public resource pages must be tested for keyboard navigation, screen reader support, and WCAG 2.2 AA compliance.

## 2.4 No Regressions

Before merging any resource implementation, the full test suite must pass.

---

# 3. Unit Testing Strategy

Unit tests focus on isolated logic, primarily calculation engines, tool engines, and model scopes.

## 3.1 Calculation Engine Tests

Each calculator PHP class must have a dedicated unit test.

Required test cases:

- **Normal values**: Verify correct output for typical inputs.
- **Boundary values**: Verify behavior at min/max limits.
- **Zero values**: Verify behavior when inputs are zero.
- **Negative values**: Verify behavior for negative inputs where applicable.
- **Missing required values**: Verify validation or safe handling.
- **Invalid types**: Verify rejection of non-numeric inputs.
- **Expected known results**: Verify against published medical/laboratory references.

## 3.2 Tool Engine Tests

Each tool PHP class must have a dedicated unit test.

Required test cases:

- Same as Calculation Engine Tests.
- **Unit conversion accuracy**: Verify correct conversions where applicable.
- **Formatting rules**: Verify output formatting and rounding.

## 3.3 Model Scope Tests

Test common model scopes:

- `scopePublished` returns only published resources.
- `scopeSearch` returns matching resources.
- Status transition methods work correctly.

## 3.4 Validation Rule Tests

Test custom validation rules:

- Slug uniqueness.
- JSON schema validity.
- Input schema structure.

---

# 4. Feature Testing Strategy (Admin CRUD)

Feature tests simulate real administrator interactions with the admin portal.

## 4.1 Authorization Tests

For each resource module, test:

- Guest cannot access admin pages.
- Inactive user cannot access admin pages.
- User without permission receives 403.
- User with permission can access pages.

## 4.2 CRUD Operation Tests

For each resource module, test:

- Index page renders with resources.
- Index page displays empty state when no resources exist.
- Create form renders.
- Store action creates resource with valid data.
- Store action rejects invalid data.
- Edit form renders with existing data.
- Update action updates resource with valid data.
- Update action rejects invalid data.
- Destroy action deletes resource.
- Archive action archives resource.

## 4.3 Validation Tests

Test that form validation works:

- Required fields are enforced.
- Unique slugs are enforced.
- JSON schemas are validated.
- Taxonomy assignments are validated.

## 4.4 Audit Logging Tests

Test that CRUD operations generate audit logs:

- Resource creation logs `resource.created`.
- Resource update logs `resource.updated`.
- Resource deletion logs `resource.deleted`.
- Sensitive fields are masked in audit logs.

---

# 5. Feature Testing Strategy (Public Pages)

Feature tests simulate real visitor interactions with the public site.

## 5.1 Listing Page Tests

For each resource module, test:

- Listing page renders successfully.
- Listing page displays only published resources.
- Listing page displays empty state when no resources exist.
- Pagination works correctly.
- Filtering by category works.
- Filtering by tag works.

## 5.2 Detail Page Tests

For each resource module, test:

- Detail page renders for published resource.
- Detail page returns 404 for unpublished resource.
- Detail page returns 404 for nonexistent slug.
- Breadcrumbs are displayed correctly.
- Meta tags are rendered correctly.
- Canonical URL is correct.

## 5.3 Interactive Resource Tests

For Calculators and Tools, test:

- Form renders with correct inputs.
- Valid inputs return correct result.
- Invalid inputs return validation errors.
- Loading state is displayed during calculation.
- Error state is displayed on engine failure.

## 5.4 SEO Tests

Test that:

- Published resources are indexable.
- Unpublished resources are not accessible.
- Structured data is valid.
- Filtered pages use canonical tags or noindex.

---

# 6. Integration Testing Strategy

Integration tests verify that resources work correctly with taxonomy and search.

## 6.1 Taxonomy Integration Tests

Test that:

- Resources can be assigned categories.
- Resources can be assigned tags.
- Filtering by category returns correct resources.
- Filtering by tag returns correct resources.
- Inactive categories are excluded from filters.

## 6.2 Search Integration Tests

Test that:

- Search returns matching calculators.
- Search returns matching tools.
- Search respects resource type filters.
- Search respects category filters.
- Search respects tag filters.
- Search returns empty state for no results.
- Search does not expose unpublished resources.

---

# 7. Accessibility Testing Strategy

Accessibility tests verify WCAG 2.2 AA compliance for resource interfaces.

## 7.1 Keyboard Navigation Tests

Test that:

- Resource cards are keyboard accessible.
- Resource forms are keyboard accessible.
- Filter controls are keyboard accessible.
- Pagination controls are keyboard accessible.
- Interactive calculators/tools are keyboard accessible.

## 7.2 Screen Reader Tests

Test that:

- Form inputs have associated labels.
- Validation errors are announced.
- Result updates are announced via aria-live.
- Breadcrumbs are announced correctly.

## 7.3 Visual Accessibility Tests

Test that:

- Color contrast meets WCAG AA standards.
- Focus states are visible.
- Touch targets are large enough.
- Reduced motion preferences are respected.

---

# 8. Performance Testing Strategy

Performance tests verify that resource queries remain efficient on shared hosting.

## 8.1 Query Optimization Tests

Test that:

- Listing pages use pagination.
- Eager loading is used for relationships.
- N+1 queries are avoided.
- Full-text indexes are used where applicable.

## 8.2 Response Time Tests

Test that:

- Listing pages load within acceptable time (< 500ms).
- Detail pages load within acceptable time (< 500ms).
- Search queries complete within acceptable time (< 500ms).

## 8.3 Asset Loading Tests

Test that:

- CSS and JS are compiled and optimized.
- Images are lazy-loaded where appropriate.
- No unnecessary assets are loaded.

---

# 9. Test Data Management

## 9.1 Factories

Create factories for all resource types:

- `CalculatorFactory`
- `LaboratoryToolFactory`
- `CategoryFactory`
- `TagFactory`

Factories must generate valid, realistic dummy data.

## 9.2 Seeders

Create seeders for test data:

- Seed published calculators.
- Seed published tools.
- Seed categories and tags.
- Seed admin users with different roles.

## 9.3 Test Isolation

- Use `RefreshDatabase` trait for feature tests.
- Use database transactions for test isolation.
- Do not rely on production data for tests.

---

# 10. Continuous Integration & Regression

## 10.1 Test Suite Execution

- Run the full test suite before merging any resource implementation.
- Run the full test suite before deploying to production.

## 10.2 Regression Prevention

- Do not merge code that breaks existing tests.
- Do not weaken test assertions to make tests pass.
- Document any intentionally skipped tests.

## 10.3 Test Coverage Goals

- Aim for high test coverage on calculation/tool engines.
- Aim for comprehensive feature test coverage on admin CRUD.
- Aim for comprehensive feature test coverage on public pages.

---

# Document Completion Check

## Requirements Covered

- Testing philosophy defined.
- Unit testing strategy defined.
- Feature testing strategy (Admin CRUD) defined.
- Feature testing strategy (Public Pages) defined.
- Integration testing strategy defined.
- Accessibility testing strategy defined.
- Performance testing strategy defined.
- Test data management defined.
- Continuous integration and regression rules defined.

## Decisions Established

- Mathematical accuracy is the highest priority for calculators/tools.
- Security and accessibility are mandatory for all resource implementations.
- The full test suite must pass before merging or deploying.
- Factories and seeders are used for test data.
- Regression prevention is enforced.

## Dependencies

- Approved Phase 02 Testing Foundation.
- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Calculators Architecture.
- Approved Phase 03 Laboratory Tools Architecture.
- Approved Phase 03 Search Implementation.

## Files Affected

This document creates:

03_Resource_Architecture/08_Resource_Testing_Strategy.md

It dictates the testing requirements for all future resource implementations.

## Open Questions

1. Should we enforce a minimum test coverage percentage in CI?
2. Should accessibility tests be automated or manual for MVP?

## TBD Decisions

- TBD: Final decision on test coverage enforcement.
- TBD: Final decision on automated accessibility testing tools.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE03-DOC08

## Context

LabGato requires a comprehensive testing strategy for all resource implementations. This document defines the testing philosophy, unit/feature/integration/accessibility/performance testing strategies, and CI/regression rules.

This prompt implements the documentation outcome of the Resource Testing Strategy document.

## Objective

Create the official LabGato Resource Testing Strategy document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding testing specification for all resource vertical slices.

## Existing Architecture

Relevant approved decisions:

- Mathematical accuracy is the highest priority.
- Security and accessibility are mandatory.
- The full test suite must pass before merging.
- Factories and seeders are used for test data.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- 03_Resource_Architecture/03_Calculators_Architecture.md
- 03_Resource_Architecture/04_Laboratory_Tools_Architecture.md
- tests/ directory structure.

## Files to Create

Create:

03_Resource_Architecture/08_Resource_Testing_Strategy.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact. This is an internal architecture document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the testing philosophy.
- The file defines unit, feature, integration, accessibility, and performance testing strategies.

## Integration Requirements

This document must be used as the primary reference for the Definition of Done in all resource vertical slices.

## Definition of Done

This implementation is complete when:

- The Resource Testing Strategy file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.