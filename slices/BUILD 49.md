labgato/
├── database/
│   └── migrations/
│       └── xxxx_create_user_profiles_table.php
├── app/
│   ├── Models/
│   │   ├── UserProfile.php
│   │   └── User.php
│   └── Http/
│       ├── Requests/
│       │   └── Account/
│       │       └── UpdateProfileRequest.php
│       └── Controllers/
│           ├── Auth/
│           │   └── RegisteredUserController.php
│           └── Account/
│               └── ProfileController.php
├── resources/
│   └── views/
│       ├── auth/
│       │   └── register.blade.php
│       ├── account/
│       │   └── profile/
│       │       └── edit.blade.php
│       └── public/
│           └── profiles/
│               └── show.blade.php
└── tests/
    └── Feature/
        └── Account/
            └── ProfileExtensionTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD49-PROFILE-EXTENSION

CONTEXT:
LabGATO is adding the ASCPI Challenge assessment system. Before building the exam engine,
we must extend user profiles with professional classification (student/professional),
institution/employer details, and privacy controls. This data powers leaderboards,
career features, and public contributor profiles.

OBJECTIVE:
Extend the user profile system with professional status, conditional student/professional
fields, profile photo, country, display name, and privacy settings.

EXISTING ARCHITECTURE:
- users table with name, email, user_type.
- Existing member profile page (/account/profile) and public contributor profiles.
- Abyss Blue theme with dark mode.

FILES TO INSPECT:
- app/Models/User.php
- resources/views/account/profile/edit.blade.php
- resources/views/auth/register.blade.php
- app/Http/Controllers/Auth/RegisteredUserController.php
- app/Http/Controllers/Account/ProfileController.php
- resources/views/public/profiles/show.blade.php

FILES TO CREATE:
- database/migrations/xxxx_create_user_profiles_table.php
- app/Models/UserProfile.php
- app/Http/Requests/Account/UpdateProfileRequest.php
- tests/Feature/Account/ProfileExtensionTest.php

FILES TO MODIFY:
- app/Models/User.php
- resources/views/auth/register.blade.php
- app/Http/Controllers/Auth/RegisteredUserController.php
- resources/views/account/profile/edit.blade.php
- app/Http/Controllers/Account/ProfileController.php
- resources/views/public/profiles/show.blade.php

TASK 1: USER PROFILES SCHEMA

Create migration: create_user_profiles_table.

Columns (hasOne relationship to users):
- id: primary key
- user_id: foreignId, unique, constrained, cascadeOnDelete
- display_name: string, nullable
- country: string, nullable
- photo_path: string, nullable
- professional_status: string, nullable
  (values: student, intern, scientist, technician, professional, researcher, lecturer, other)
- institution: string, nullable        (student)
- programme: string, nullable          (student)
- level: string, nullable              (student)
- graduation_year: integer, nullable   (student)
- employer: string, nullable           (professional)
- job_title: string, nullable          (professional)
- years_of_experience: integer, nullable (professional)
- specialization: string, nullable     (professional)
- show_country: boolean, default true
- show_institution: boolean, default true
- show_on_leaderboard: boolean, default true
- profile_completed: boolean, default false (computed or stored)
- timestamps

TASK 2: MODELS

Create app/Models/UserProfile.php:
- Fillable fields.
- Casts: booleans for privacy flags, graduation_year/years_of_experience as integer.
- belongsTo(User).

Modify app/Models/User.php:
- hasOne(UserProfile).
- Helper: profile() with withDefault() so $user->profile never null.
- Helper: isStudent(), isProfessional().
- On user creation, auto-create an empty profile (via created event or in controller).

TASK 3: REGISTRATION — COLLECT PROFESSIONAL STATUS

Modify register.blade.php:
- Add a "Professional Status" select (required) with the 8 options.
- Keep registration fast: only collect status at signup; full details later.

Modify RegisteredUserController:
- Validate professional_status.
- After creating user, create profile with professional_status.
- Redirect to dashboard with a "Complete your profile" prompt.

TASK 4: PROFILE EDIT — CONDITIONAL FIELDS

Modify account/profile/edit.blade.php:
- Photo upload (avatar preview, max 2MB, image only).
- Display name, country.
- Professional status select.
- CONDITIONAL: if status == student, show institution/programme/level/graduation_year.
- CONDITIONAL: if status in [scientist, technician, professional, researcher, lecturer],
  show employer/job_title/years_of_experience/specialization.
- Use Alpine.js x-show to toggle these groups live.
- Privacy toggles: show_country, show_institution, show_on_leaderboard.
- Profile completion indicator (percentage) encouraging full profile.

Create UpdateProfileRequest:
- Validate all fields, image rules for photo, integer ranges for years.

Modify ProfileController:
- update() saves profile + stores photo (storage/app/public/profiles).
- Compute profile_completed.

TASK 5: PUBLIC PROFILE — PRIVACY-AWARE DISPLAY

Modify public/profiles/show.blade.php:
- Show display_name or name, photo, professional_status badge.
- Show country ONLY if show_country.
- Show institution/employer ONLY if show_institution.
- Respect privacy flags strictly.

TASK 6: TESTS

Create tests/Feature/Account/ProfileExtensionTest.php:
- Test registration requires professional_status and creates a profile.
- Test student sees/edits student fields; professional sees professional fields.
- Test photo upload works.
- Test privacy flags hide country/institution on public profile.
- Test show_on_leaderboard flag stored.

DEFINITION OF DONE:
- user_profiles table created with all fields.
- Registration collects professional status.
- Profile edit shows conditional student/professional fields (Alpine toggle).
- Photo upload works.
- Privacy flags control public display.
- Public profile respects privacy.
- All tests pass.