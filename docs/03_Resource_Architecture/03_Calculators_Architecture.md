# 03 — Calculators Architecture

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Target Users and Journeys).
- The approved Phase 02 documentation (specifically Database Foundation, RBAC Implementation, Testing Foundation).
- The approved Phase 03 Resource Foundation document.
- The approved Phase 03 Taxonomy Implementation document.

## Defines

This document defines:

- The Calculator resource data model.
- The Input Schema architecture for dynamic form generation.
- The Output Schema and rounding rules.
- The Calculation Engine strategy for safe, non-arbitrary execution.
- The validation rules for calculator inputs.
- The administrative management patterns for calculators.
- The public UI and interaction requirements.
- The testing and verification strategy for mathematical accuracy.

## Affects

This document affects:

- The Calculators database migration.
- The Calculator Eloquent model.
- The Calculator Admin CRUD interface.
- The Calculator Public Detail page.
- The Calculation Service layer.
- The Search Implementation document.
- The Resource Testing Strategy document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a secure, testable architecture for laboratory calculations.
- Prevents the execution of arbitrary administrator-provided code.
- Ensures mathematical accuracy through structured input/output definitions and mandatory testing.
- Provides the blueprint for the first complete resource vertical slice.

---

# 1. Purpose

This document establishes the Calculators Architecture for LABGOTO.

Calculators are high-value, high-risk resources. A calculation error can lead to professional mistakes. Therefore, the architecture must prioritize mathematical accuracy, input validation, and security over arbitrary flexibility.

LABGOTO calculators must never execute raw PHP, JavaScript, or database-stored code snippets directly. All calculation logic must be encapsulated in safe, tested, and version-controlled services.

---

# 2. Calculator Data Model

The `calculators` table must extend the common resource attributes defined in the Resource Foundation.

## 2.1 Additional Columns

In addition to the common attributes (`id`, `name`, `slug`, `status`, etc.), the `calculators` table must include:

formula_display
- Type: string
- Nullable: true
- Description: A human-readable representation of the formula (e.g., "Na - (Cl + HCO3)").

input_schema
- Type: json
- Nullable: false
- Description: Defines the input fields, labels, units, and validation rules.

output_schema
- Type: json
- Nullable: false
- Description: Defines the result fields, labels, units, and rounding rules.

engine
- Type: string
- Default: 'php_class'
- Description: The calculation engine type.

engine_reference
- Type: string
- Nullable: false
- Description: The reference to the calculation logic (e.g., the PHP class name).

interpretation_notes
- Type: text
- Nullable: true
- Description: Educational context on how to interpret the result.

references
- Type: text
- Nullable: true
- Description: Scientific citations or textbook references.

## 2.2 Relationships

- `categories()`: belongsToMany Category.
- `tags()`: belongsToMany Tag.
- `createdBy()`: belongsTo User.
- `updatedBy()`: belongsTo User.

---

# 3. Input Schema Architecture

The `input_schema` JSON column defines the form fields rendered on the public calculator page.

## 3.1 Input Field Structure

Each input field in the schema must define:

- `key`: Unique identifier (e.g., "sodium").
- `label`: Display label (e.g., "Serum Sodium").
- `type`: Input type (number, select).
- `unit`: Unit of measurement (e.g., "mEq/L").
- `required`: Boolean.
- `min`: Minimum allowed value (optional).
- `max`: Maximum allowed value (optional).
- `step`: Step value for number inputs (optional).
- `options`: Array of options if type is select.
- `placeholder`: Placeholder text.

## 3.2 Example Input Schema

[
  {
    "key": "sodium",
    "label": "Serum Sodium",
    "type": "number",
    "unit": "mEq/L",
    "required": true,
    "min": 100,
    "max": 200,
    "step": 0.1
  },
  {
    "key": "chloride",
    "label": "Serum Chloride",
    "type": "number",
    "unit": "mEq/L",
    "required": true,
    "min": 80,
    "max": 150,
    "step": 0.1
  }
]

---

# 4. Output Schema & Rounding Rules

The `output_schema` JSON column defines how the result is displayed.

## 4.1 Output Field Structure

Each output field in the schema must define:

- `key`: Unique identifier (e.g., "anion_gap").
- `label`: Display label (e.g., "Anion Gap").
- `unit`: Unit of measurement (e.g., "mEq/L").
- `rounding`: Rounding method (none, round_up, round_down, round_half_up).
- `precision`: Number of decimal places (e.g., 1, 2).
- `reference_range`: Optional string or link to a reference range.

## 4.2 Example Output Schema

[
  {
    "key": "anion_gap",
    "label": "Anion Gap",
    "unit": "mEq/L",
    "rounding": "round_half_up",
    "precision": 1
  }
]

## 4.3 Rounding Rules

- `none`: No rounding applied.
- `round_up`: Always round up (ceil).
- `round_down`: Always round down (floor).
- `round_half_up`: Standard rounding (round).

The calculation engine must apply these rules strictly before returning the result.

---

# 5. Calculation Engine Strategy

To ensure security and testability, LABGOTO will use a **Code-Based Calculation Engine** for the MVP.

## 5.1 Engine Types

- `php_class`: The calculation logic is implemented in a dedicated PHP class.
- `expression`: (Future) The calculation logic is defined using a safe, sandboxed expression parser.

For the MVP, only `php_class` is approved.

## 5.2 PHP Class Engine

Each calculator must have a corresponding PHP class that implements a `CalculatorInterface`.

Location:

app/Services/Calculators/

Example:

app/Services/Calculators/AnionGapCalculator.php

## 5.3 CalculatorInterface

The interface must define:

public function calculate(array $inputs): array;

- `$inputs`: An associative array of validated input values.
- Returns: An associative array of calculated output values.

## 5.4 Engine Resolution

The `CalculatorService` must resolve the `engine_reference` to the actual PHP class.

Rules:
- The `engine_reference` must be a valid class name.
- The class must exist and implement `CalculatorInterface`.
- The class must be whitelisted or autoloadable.
- Do not allow arbitrary class instantiation from user input. The `engine_reference` is managed by administrators, but should still be validated against a known list or namespace.

## 5.5 Security Rules

- Do not use `eval()`.
- Do not use `create_function()`.
- Do not execute database-stored code strings.
- All calculation logic must be version-controlled in PHP files.

---

# 6. Validation Rules

Calculator inputs must be validated server-side before calculation.

## 6.1 Input Validation

- All required fields must be present.
- Numeric fields must be numeric.
- Values must be within `min` and `max` bounds defined in the input schema.
- Select fields must match one of the defined options.

## 6.2 Validation Errors

If validation fails:
- Return a 422 Unprocessable Entity response (for API/Livewire).
- Display inline validation errors on the public form.
- Do not execute the calculation engine.

---

# 7. Admin Management

Administrators with `calculators.create` and `calculators.edit` permissions can manage calculators.

## 7.1 Calculator Form

The admin form must include:
- Common resource fields (name, slug, status, etc.).
- Formula display field.
- Engine selection (default: PHP Class).
- Engine reference field (dropdown of available calculator classes or text input for Super Admin).
- Input schema editor (JSON editor or structured form builder).
- Output schema editor (JSON editor or structured form builder).
- Interpretation notes.
- References.
- Taxonomy assignment (Categories, Tags).

## 7.2 Schema Editing

For the MVP, a JSON textarea editor is acceptable for input/output schemas.
A visual form builder for schemas may be implemented in a future phase.

## 7.3 Testing Integration

When creating or editing a calculator, the admin should be prompted to verify the calculation logic in the corresponding PHP class.

---

# 8. Public UI & Interaction

## 8.1 Calculator Detail Page

The public calculator page must include:
- Resource header (title, breadcrumbs, meta).
- Calculator description.
- Interactive calculator form.
- Result display area.
- Interpretation notes.
- References.
- Related resources.

## 8.2 Calculator Form

- Render inputs dynamically based on `input_schema`.
- Use appropriate HTML input types (`number`, `select`).
- Include unit labels.
- Include client-side validation (optional) and server-side validation (mandatory).
- Submit button: "Calculate".

## 8.3 Result Display

- Display results dynamically based on `output_schema`.
- Show units.
- Apply rounding rules.
- Display reference ranges if available.
- Use a clear, readable layout.

## 8.4 Loading and Error States

- Show a loading state during calculation.
- Show validation errors inline.
- Show a safe error message if the calculation engine fails.
- Do not expose stack traces.

---

# 9. Testing & Verification

Calculators must be rigorously tested.

## 9.1 Unit Tests

Each calculator PHP class must have a dedicated unit test.

Required test cases:
- Normal values.
- Boundary values (min/max).
- Zero values.
- Negative values (if applicable).
- Missing required values.
- Invalid types.
- Expected known results.

## 9.2 Feature Tests

- Test that the public calculator page renders.
- Test that valid inputs return the correct result.
- Test that invalid inputs return validation errors.
- Test that unauthorized users cannot access admin calculator CRUD.

## 9.3 Rounding Tests

- Verify that the rounding rules defined in the output schema are applied correctly.

---

# Document Completion Check

## Requirements Covered

- Calculator data model defined.
- Input schema architecture defined.
- Output schema and rounding rules defined.
- Calculation engine strategy defined (Code-Based).
- Validation rules defined.
- Admin management patterns defined.
- Public UI and interaction requirements defined.
- Testing and verification strategy defined.

## Decisions Established

- Calculators use a Code-Based Calculation Engine for MVP.
- Arbitrary code execution is prohibited.
- Input and output schemas are stored as JSON.
- Rounding rules are defined in the output schema.
- Calculator logic is implemented in PHP classes.
- Unit tests are mandatory for every calculator.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Taxonomy Implementation.
- Approved Phase 02 Testing Foundation.

## Files Affected

This document creates:

03_Resource_Architecture/03_Calculators_Architecture.md

It dictates the implementation of the calculators table, Calculator model, CalculatorService, and admin/public interfaces.

## Open Questions

1. Should the admin panel include a visual schema builder for inputs/outputs in the MVP, or is a JSON editor sufficient?
2. Should we support unit conversions automatically within the calculator engine?

## TBD Decisions

- TBD: Final decision on visual schema builder vs. JSON editor.
- TBD: Future unit conversion strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE03-DOC03

## Context

LABGOTO requires a secure and accurate architecture for laboratory calculators. This document defines the data model, schema structure, safe calculation engine, and testing requirements.

This prompt implements the documentation outcome of the Calculators Architecture document.

## Objective

Create the official LABGOTO Calculators Architecture document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding specification for the first resource vertical slice.

## Existing Architecture

Relevant approved decisions:

- Code-Based Calculation Engine is approved for MVP.
- Arbitrary code execution is prohibited.
- Input/output schemas are stored as JSON.
- Unit tests are mandatory.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- 03_Resource_Architecture/02_Taxonomy_Implementation.md
- app/Services/ directory structure.

## Files to Create

Create:

03_Resource_Architecture/03_Calculators_Architecture.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the calculator architecture.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements for calculators.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that the prohibition of arbitrary code execution is clearly documented.

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
- The file clearly defines the Code-Based Calculation Engine.
- The file defines the input/output schema structure.

## Integration Requirements

This document must be used as the primary reference when building the Calculators vertical slice.

## Definition of Done

This implementation is complete when:

- The Calculators Architecture file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.