# 07 — Governance and Change Control

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Glossary.
- The approved Phase 01 file tree.

## Defines

This document defines:

- The official source-of-truth hierarchy.
- The mandatory stop-on-conflict rule.
- The formal change request process.
- The documentation phase rules.
- The mandatory document structure rules.
- The decision log maintenance rules.
- The AI behavior constraints regarding architectural guessing.
- The scope boundary enforcement rules.

## Affects

This document affects:

- Every future documentation phase.
- Every future implementation prompt.
- Every future architectural decision.
- How the AI assistant behaves when faced with missing information or conflicting requirements.
- How the human project owner reviews and approves changes.
- The integrity of the entire LABGOTO system over time.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the strict rules of engagement for building LABGOTO.
- Prevents silent architectural degradation.
- Prevents scope creep.
- Ensures that every change to the system is deliberate, documented, and approved.

---

# 1. Purpose

This document establishes the governance and change control framework for LABGOTO.

Because LABGOTO is a complex, multi-layered software product, it requires strict rules to ensure that documentation, code, database design, and UI remain consistent over time.

Without governance, small undocumented changes eventually lead to a fragmented, unmaintainable system.

This document defines how decisions are made, how conflicts are resolved, and how changes are approved.

---

# 2. Source-of-Truth Hierarchy

When documentation, requirements, or AI assumptions conflict, the following hierarchy must be used to determine the correct path.

## Level 1: Latest Approved Architectural Decision

Explicitly approved architectural decisions (e.g., "We will use Laravel," "We will use MySQL," "Calculators must not execute arbitrary code") are the highest authority.

## Level 2: Current Master Architecture

The approved master architecture documents (like this Phase 01 collection) override older or feature-specific documents if a direct, unresolved contradiction exists.

## Level 3: Latest Approved File Tree

The approved file tree for a phase dictates what must be built and documented. Extra files must not be added silently.

## Level 4: Latest Feature-Specific Documentation

Detailed feature documents (e.g., "Calculator Architecture") override general guidelines when dealing with that specific feature.

## Level 5: Earlier Documentation

Older documentation remains valid unless explicitly superseded by a newer approved document or a formal change request.

## Level 6: AI Assumptions

AI assumptions are the lowest level of authority.

**AI assumptions must NEVER override approved project decisions.**

If the AI does not know something, it must mark it as `TBD — Architectural Decision Required` rather than guessing and silently implementing a solution.

---

# 3. The Stop-on-Conflict Rule

If a new requirement, user request, or implementation discovery materially conflicts with existing approved architecture, the following rule is mandatory:

**DO NOT IMPLEMENT THE CONFLICTING FEATURE.**

Instead, the AI or developer must:

1. **Stop** the current implementation or documentation generation.
2. **Identify** the exact conflict.
3. **Reference** the existing approved decision that is being contradicted.
4. **Explain** the new requirement or discovery.
5. **Explain** the technical or architectural conflict.
6. **Provide** possible solutions (e.g., Change Request, revert to old rule, hybrid approach).
7. **Recommend** the best option.
8. **Identify** affected documentation and files.
9. **Wait** for explicit approval from the project owner.

Major architectural conflicts must never be resolved through guesswork or silent code changes.

---

# 4. Change Request Process

Any proposed change to approved scope, architecture, database design, API structure, or security policy must be documented as a Change Request.

## 4.1 Change Request Fields

Every Change Request must include:

- **Change ID:** A unique identifier (e.g., `LABGOTO-CR-0001`).
- **Existing Decision:** What is currently approved and documented.
- **Proposed Change:** What is being suggested.
- **Reason:** Why the change is needed.
- **Impact:** How this affects the overall system.
- **Files Affected:** Existing and new documentation/code files.
- **Database Impact:** Required migrations, data changes, or schema alterations.
- **API Impact:** Changes to endpoints, contracts, or routes.
- **Security Impact:** Implications for authentication, authorization, or data protection.
- **SEO Impact:** Implications for indexing, URLs, or metadata.
- **Accessibility Impact:** Implications for WCAG compliance or user interaction.
- **Migration Plan:** How existing functionality or data will transition.
- **Approval Requirement:** Explicit statement that approval is required before implementation.

## 4.2 Change Request Execution

No change request may be implemented until it is explicitly approved by the project owner.

Once approved, the Change Request must be recorded in the project's Decision Log, and all affected documentation must be updated before implementation begins.

---

# 5. Documentation Phase Rules

LABGOTO is built through structured documentation phases.

## 5.1 Phase Initiation

Before writing any documents for a new phase, the AI must present:

1. **Phase Overview:** Purpose, objectives, and dependencies.
2. **File Tree:** The exact folder and file names to be created.
3. **File Purposes:** A brief description of what each file will cover.
4. **File Count:** The explicit total number of files in the phase.

## 5.2 Phase Approval

The documentation phase must not begin until the file tree and file count are approved by the project owner.

## 5.3 Phase Execution

Documents must be written one by one, or in approved logical groups, exactly as defined in the approved file tree.

## 5.4 No Silent Additions

Extra files must not be added halfway through a phase. If a new file becomes strictly necessary, the AI must stop, explain why, and request approval to amend the file tree and file count.

## 5.5 Phase Completion

A phase is only complete when:

- All planned files exist.
- All documents are internally consistent.
- Dependencies are documented.
- No unresolved major contradictions exist.
- Implementation prompts exist for each relevant document.
- The Phase Implementation Map and Build Order are provided.

---

# 6. Document Structure Rules

Every architectural and feature document in LABGOTO must follow a strict structure to ensure consistency and traceability.

## 6.1 Mandatory Header Sections

Every document must begin with:

- **Depends On:** Previous decisions/documents required.
- **Defines:** What this document establishes.
- **Affects:** Which future areas depend on it.
- **Implementation Impact:** What will eventually need to be built or how this guides code.

## 6.2 Mandatory Footer Sections

Every document must end with:

- **Document Completion Check:**
  - Requirements covered
  - Decisions established
  - Dependencies
  - Files affected
  - Open questions
  - TBD decisions
- **Implementation Prompt:**
  - Context, Objective, Existing Architecture, Files to Inspect/Create/Modify, Database/API/UI/Security/Accessibility/SEO/Analytics/Testing/Integration requirements, and Definition of Done.

If a document does not directly result in code (e.g., a glossary or charter), the Implementation Prompt must describe the repository/documentation action required to formalize it.

---

# 7. Decision Log Maintenance

The project must maintain a continuous record of approved decisions.

## 7.1 What Must Be Logged

- Technology stack selections.
- Scope inclusions and exclusions.
- Database engine and driver selections.
- Hosting and deployment constraints.
- Approvals of specific architectural patterns (e.g., RBAC model, AI provider abstraction).
- Approvals of Change Requests.

## 7.2 Where It Is Logged

Decisions are initially logged within the `Project Charter` or specific architecture documents.

As the project grows, a dedicated `Decision_Log.md` or Architecture Decision Records (ADR) folder may be introduced in later phases.

## 7.3 Reversing Decisions

A decision can only be reversed through a formal Change Request. The original decision must remain visible in the history to explain why the system was built a certain way initially.

---

# 8. AI Behavior Constraints

The AI assisting with LABGOTO must operate under strict constraints to protect the architecture.

## 8.1 No Architectural Guessing

If information is genuinely missing, the AI must mark it as `TBD — Architectural Decision Required`. It must not silently choose a framework, database relationship, or security policy.

## 8.2 No Scope Creep

The AI must not introduce features, pages, or user roles that are not in the approved scope or product areas.

## 8.3 No Disconnected Implementation

The AI must not generate code for a feature unless the underlying architecture (database, API, permissions) has been documented and approved.

## 8.4 Respect the File Tree

The AI must not rename folders, move files, or alter the approved documentation structure without a Change Request.

---

# 9. Scope Boundary Enforcement

The governance framework actively protects the project from unrelated systems.

## 9.1 Protected Boundaries

The following are protected boundaries that require a Change Request to cross:

- Introducing patient management features.
- Introducing hospital/LIS workflows.
- Introducing e-commerce, billing, or payment gateways.
- Introducing the superseded Quote Wizard or Lead Generation features.
- Introducing unrestricted, un-logged AI chatbots.

## 9.2 Enforcement Mechanism

If a user prompt requests a feature outside the approved scope, the AI must:

1. Acknowledge the request.
2. Identify that it falls outside the current approved scope (referencing Document 03: Product Scope and Non-Goals).
3. Offer to draft a Change Request to evaluate adding it to the scope.
4. Refuse to implement it silently.

---

# Document Completion Check

## Requirements Covered

- Source-of-truth hierarchy defined.
- Stop-on-conflict rule defined.
- Change request process defined.
- Documentation phase rules defined.
- Document structure rules defined.
- Decision log maintenance defined.
- AI behavior constraints defined.
- Scope boundary enforcement defined.

## Decisions Established

- AI assumptions are the lowest level of authority.
- Conflicts must stop implementation until resolved.
- Changes require formal documentation and approval.
- Documentation phases require approved file trees before writing.
- Every document must have standardized header and footer sections.
- Scope boundaries are actively protected from creep.

## Dependencies

- Approved LABGOTO Master Directive.
- Approved Project Charter.
- Approved Product Scope and Non-Goals.
- Approved Glossary.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/07_Governance_and_Change_Control.md

It governs the creation and modification of all future files in the project.

## Open Questions

1. Will the project use a dedicated ADR (Architecture Decision Record) folder in later phases, or keep decisions inline in documents?
2. What is the exact format for tracking approved Change Requests in the repository?

## TBD Decisions

- TBD: Final ADR folder structure and naming convention.
- TBD: Automated tooling for enforcing documentation rules (e.g., Markdown linting scripts).

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC07

## Context

LABGOTO requires a strict governance framework to ensure that the AI assistant and human developers maintain architectural integrity over time. This document establishes the rules for decision-making, conflict resolution, and change control.

This prompt implements the documentation outcome of the Governance and Change Control document.

## Objective

Create the official LABGOTO Governance and Change Control document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository will have a binding set of rules that govern all future documentation and implementation work.

## Existing Architecture

Relevant approved decisions:

- Documentation controls implementation.
- AI assumptions cannot override approved decisions.
- Scope boundaries are strictly enforced.
- Changes require formal approval.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Documents 01 through 06.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/07_Governance_and_Change_Control.md

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

No public SEO impact. This is an internal governance document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the Source-of-Truth Hierarchy.
- The file clearly defines the Stop-on-Conflict rule.

## Integration Requirements

This document must be treated as binding rules by the AI assistant in all future prompts and phases.

## Definition of Done

This implementation is complete when:

- The Governance and Change Control file exists.
- The file matches the approved Phase 01 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.