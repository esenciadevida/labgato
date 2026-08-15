# 04 — Laboratory Tools Architecture

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Target Users and Journeys).
- The approved Phase 02 documentation (specifically Database Foundation, RBAC Implementation, Testing Foundation).
- The approved Phase 03 Resource Foundation document.
- The approved Phase 03 Taxonomy Implementation document.
- The approved Phase 03 Calculators Architecture document.

## Defines

This document defines:

- The Laboratory Tool resource data model.
- The Input Schema architecture for dynamic tool forms.
- The Output Schema architecture for tool results.
- The Tool Engine strategy for safe, non-arbitrary execution.
- The validation rules for tool inputs.
- The administrative management patterns for tools.
- The public UI and interaction requirements.
- The testing and verification strategy for tool accuracy.

## Affects

This document affects:

- The Laboratory Tools database migration.
- The LaboratoryTool Eloquent model.
- The Laboratory Tool Admin CRUD interface.
- The Laboratory Tool Public Detail page.
- The Tool Service layer.
- The Search Implementation document.
- The Resource Testing Strategy document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a secure, testable architecture for laboratory utilities.
- Prevents the execution of arbitrary administrator-provided code.
- Ensures tool accuracy through structured input/output definitions and mandatory testing.
- Provides the blueprint for the second resource vertical slice.

---

# 1. Purpose

This document establishes the Laboratory Tools Architecture for LABGOTO.

Laboratory Tools are practical, interactive utilities used for daily laboratory operations, preparation, and general calculations that do not necessarily involve direct patient diagnostic formulas. Examples include unit converters, dilution helpers, and centrifugation calculators.

Like Calculators, Laboratory Tools must prioritize accuracy, input validation, and security. All tool logic must be encapsulated in safe, tested, and version-controlled services.

---

# 2. Laboratory Tool Data Model

The `laboratory_tools` table must extend the common resource attributes defined in the Resource Foundation.

## 2.1 Additional Columns

In addition to the common attributes (`id`, `name`, `slug`, `status`, etc.), the `laboratory_tools` table must include:

tool_type
- Type: string
- Nullable: true
- Description: A classification for the tool (e.g., "converter", "preparation", "general").

input_schema
- Type: json
- Nullable: false
- Description: Defines the input fields, labels, units, and validation rules.

output_schema
- Type: json
- Nullable: false
- Description: Defines the result fields, labels, units, and formatting rules.

engine
- Type: string
- Default: 'php_class'
- Description: The tool execution engine type.

engine_reference
- Type: string
- Nullable: false
- Description: The reference to the tool logic (e.g., the PHP class name).

instructions
- Type: text
- Nullable: true
- Description: Step-by-step instructions on how to use the tool.

references
- Type: text
- Nullable: true
- Description: Scientific citations or technical references.

## 2.2 Relationships

- `categories()`: belongsToMany Category.
- `tags()`: belongsToMany Tag.
- `createdBy()`: belongsTo User.
- `updatedBy()`: belongsTo User.

---

# 3. Input Schema Architecture

The `input_schema` JSON column defines the form fields rendered on the public tool page.

## 3.1 Input Field Structure

Each input field in the schema must define:

- `key`: Unique identifier (e.g., "volume").
- `label`: Display label (e.g., "Volume").
- `type`: Input type (number, select, text).
- `unit`: Unit of measurement (e.g., "mL").
- `required`: Boolean.
- `min`: Minimum allowed value (optional).
- `max`: Maximum allowed value (optional).
- `step`: Step value for number inputs (optional).
- `options`: Array of options if type is select.
- `placeholder`: Placeholder text.

## 3.2 Example Input Schema

[
  {
    "key": "concentration",
    "label": "Stock Concentration",
    "type": "number",
    "unit": "mg/mL",
    "required": true,
    "min": 0,
    "step": 0.01
  },
  {
    "key": "volume",
    "label": "Final Volume",
    "type": "number",
    "unit": "mL",
    "required": true,
    "min": 0,
    "step": 0.1
  }
]

---

# 4. Output Schema Architecture

The `output_schema` JSON column defines how the tool result is displayed.

## 4.1 Output Field Structure

Each output field in the schema must define:

- `key`: Unique identifier (e.g., "required_mass").
- `label`: Display label (e.g., "Required Mass").
- `unit`: Unit of measurement (e.g., "mg").
- `rounding`: Rounding method (none, round_up, round_down, round_half_up).
- `precision`: Number of decimal places (e.g., 2).
- `format`: Optional formatting rule (e.g., "scientific", "standard").

## 4.2 Example Output Schema

[
  {
    "key": "required_mass",
    "label": "Required Mass",
    "unit": "mg",
    "rounding": "round_half_up",
    "precision": 2
  }
]

## 4.3 Formatting Rules

- `standard`: Display as a regular number.
- `scientific`: Display in scientific notation if the value is very large or very small.

The tool engine must apply these formatting rules before returning the result.

---

# 5. Tool Engine Strategy

To ensure security and testability, LABGOTO will use a **Code-Based Tool Engine** for the MVP.

## 5.1 Engine Types

- `php_class`: The tool logic is implemented in a dedicated PHP class.
- `expression`: (Future) The tool logic is defined using a safe, sandboxed expression parser.

For the MVP, only `php_class` is approved.

## 5.2 PHP Class Engine

Each tool must have a corresponding PHP class that implements a `ToolInterface`.

Location:

app/Services/Tools/

Example:

app/Services/Tools/DilutionCalculatorTool.php

## 5.3 ToolInterface

The interface must define:

public function execute(array $inputs): array;

- `$inputs`: An associative array of validated input values.
- Returns: An associative array of calculated output values.

## 5.4 Engine Resolution

The `ToolService` must resolve the `engine_reference` to the actual PHP class.

Rules:
- The `engine_reference` must be a valid class name.
- The class must exist and implement `ToolInterface`.
- The class must be whitelisted or autoloadable.
- Do not allow arbitrary class instantiation from user input.

## 5.5 Security Rules

- Do not use `eval()`.
- Do not use `create_function()`.
- Do not execute database-stored code strings.
- All tool logic must be version-controlled in PHP files.

---

# 6. Validation Rules

Tool inputs must be validated server-side before execution.

## 6.1 Input Validation

- All required fields must be present.
- Numeric fields must be numeric.
- Values must be within `min` and `max` bounds defined in the input schema.
- Select fields must match one of the defined options.

## 6.2 Validation Errors

If validation fails:
- Return a 422 Unprocessable Entity response (for API/Livewire).
- Display inline validation errors on the public form.
- Do not execute the tool engine.

---

# 7. Admin Management

Administrators with `tools.create` and `tools.edit` permissions can manage tools.

## 7.1 Tool Form

The admin form must include:
- Common resource fields (name, slug, status, etc.).
- Tool type field.
- Engine selection (default: PHP Class).
- Engine reference field (dropdown of available tool classes or text input for Super Admin).
- Input schema editor (JSON editor or structured form builder).
- Output schema editor (JSON editor or structured form builder).
- Instructions.
- References.
- Taxonomy assignment (Categories, Tags).

## 7.2 Schema Editing

For the MVP, a JSON textarea editor is acceptable for input/output schemas.
A visual form builder for schemas may be implemented in a future phase.

## 7.3 Testing Integration

When creating or editing a tool, the admin should be prompted to verify the tool logic in the corresponding PHP class.

---

# 8. Public UI & Interaction

## 8.1 Tool Detail Page

The public tool page must include:
- Resource header (title, breadcrumbs, meta).
- Tool description.
- Interactive tool form.
- Result display area.
- Instructions.
- References.
- Related resources.

## 8.2 Tool Form

- Render inputs dynamically based on `input_schema`.
- Use appropriate HTML input types (`number`, `select`, `text`).
- Include unit labels.
- Include client-side validation (optional) and server-side validation (mandatory).
- Submit button: "Execute" or "Calculate".

## 8.3 Result Display

- Display results dynamically based on `output_schema`.
- Show units.
- Apply formatting and rounding rules.
- Use a clear, readable layout.

## 8.4 Loading and Error States

- Show a loading state during execution.
- Show validation errors inline.
- Show a safe error message if the tool engine fails.
- Do not expose stack traces.

---

# 9. Testing & Verification

Laboratory Tools must be rigorously tested.

## 9.1 Unit Tests

Each tool PHP class must have a dedicated unit test.

Required test cases:
- Normal values.
- Boundary values (min/max).
- Zero values.
- Negative values (if applicable).
- Missing required values.
- Invalid types.
- Expected known results.

## 9.2 Feature Tests

- Test that the public tool page renders.
- Test that valid inputs return the correct result.
- Test that invalid inputs return validation errors.
- Test that unauthorized users cannot access admin tool CRUD.

## 9.3 Formatting Tests

- Verify that the formatting and rounding rules defined in the output schema are applied correctly.

---

# Document Completion Check

## Requirements Covered

- Laboratory Tool data model defined.
- Input schema architecture defined.
- Output schema architecture defined.
- Tool engine strategy defined (Code-Based).
- Validation rules defined.
- Admin management patterns defined.
- Public UI and interaction requirements defined.
- Testing and verification strategy defined.

## Decisions Established

- Laboratory Tools use a Code-Based Tool Engine for MVP.
- Arbitrary code execution is prohibited.
- Input and output schemas are stored as JSON.
- Formatting rules are defined in the output schema.
- Tool logic is implemented in PHP classes.
- Unit tests are mandatory for every tool.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Taxonomy Implementation.
- Approved Phase 03 Calculators Architecture.
- Approved Phase 02 Testing Foundation.

## Files Affected

This document creates:

03_Resource_Architecture/04_Laboratory_Tools_Architecture.md

It dictates the implementation of the laboratory_tools table, LaboratoryTool model, ToolService, and admin/public interfaces.

## Open Questions

1. Should the admin panel include a visual schema builder for inputs/outputs in the MVP, or is a JSON editor sufficient?
2. Should tools support multi-step wizards for complex preparations?

## TBD Decisions

- TBD: Final decision on visual schema builder vs. JSON editor.
- TBD: Future multi-step wizard strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE03-DOC04

## Context

LABGOTO requires a secure and accurate architecture for laboratory tools. This document defines the data model, schema structure, safe tool engine, and testing requirements.

This prompt implements the documentation outcome of the Laboratory Tools Architecture document.

## Objective

Create the official LABGOTO Laboratory Tools Architecture document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding specification for the second resource vertical slice.

## Existing Architecture

Relevant approved decisions:

- Code-Based Tool Engine is approved for MVP.
- Arbitrary code execution is prohibited.
- Input/output schemas are stored as JSON.
- Unit tests are mandatory.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- 03_Resource_Architecture/02_Taxonomy_Implementation.md
- 03_Resource_Architecture/03_Calculators_Architecture.md
- app/Services/ directory structure.

## Files to Create

Create:

03_Resource_Architecture/04_Laboratory_Tools_Architecture.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the tool architecture.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements for tools.

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
- The file clearly defines the Code-Based Tool Engine.
- The file defines the input/output schema structure.

## Integration Requirements

This document must be used as the primary reference when building the Laboratory Tools vertical slice.

## Definition of Done

This implementation is complete when:

- The Laboratory Tools Architecture file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.