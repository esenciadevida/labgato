labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PublicSite/
│   │   │   │   └── ContactController.php
│   │   │   ├── Admin/
│   │   │   │   └── ContactMessageController.php
│   │   │   └── Member/
│   │   │       └── MessageController.php
│   │   └── Requests/
│   │       ├── ContactRequest.php
│   │       └── Admin/
│   │           └── ReplyContactRequest.php
│   └── Models/
│       ├── ContactMessage.php
│       └── ContactReply.php
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_contact_messages_table.php
│   │   └── xxxx_create_contact_replies_table.php
│   └── seeders/
│       └── RolePermissionSeeder.php
├── resources/
│   └── views/
│       ├── public/
│       │   └── contact.blade.php
│       ├── admin/
│       │   └── contact-messages/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       ├── member/
│       │   └── messages.blade.php
│       └── components/
│           ├── member/
│           │   └── navigation.blade.php
│           └── admin/
│               └── sidebar.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
└── tests/
    └── Feature/
        ├── PublicSite/
        │   └── ContactFormTest.php
        ├── Admin/
        │   └── ContactMessageTest.php
        └── Member/
            └── MemberMessagesTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD32-CONTACT-US

CONTEXT:
LabGato has a notification system (Build 31) with NewContactMessageNotification and ContactMessageRepliedNotification already prepared. We are now implementing the Contact Us system.

OBJECTIVE:
Implement a public contact form, an admin inbox for Super Admins, reply functionality, and member message history. Wire notifications for new messages and replies.

EXISTING ARCHITECTURE:
- Notification system with database + mail channels.
- AdminNotifier helper for notifying admins.
- Member area with navigation.
- Hostinger shared hosting compatible.

FILES TO INSPECT:
- app/Notifications/NewContactMessageNotification.php
- app/Notifications/ContactMessageRepliedNotification.php
- app/Support/AdminNotifier.php
- app/Models/User.php
- resources/views/components/member/navigation.blade.php
- resources/views/components/admin/sidebar.blade.php
- routes/web.php
- routes/admin.php

FILES TO CREATE:
- database/migrations/xxxx_create_contact_messages_table.php
- database/migrations/xxxx_create_contact_replies_table.php
- app/Models/ContactMessage.php
- app/Models/ContactReply.php
- app/Http/Controllers/PublicSite/ContactController.php
- app/Http/Controllers/Admin/ContactMessageController.php
- app/Http/Controllers/Member/MessageController.php
- app/Http/Requests/ContactRequest.php
- app/Http/Requests/Admin/ReplyContactRequest.php
- resources/views/public/contact.blade.php
- resources/views/admin/contact-messages/index.blade.php
- resources/views/admin/contact-messages/show.blade.php
- resources/views/member/messages.blade.php
- tests/Feature/PublicSite/ContactFormTest.php
- tests/Feature/Admin/ContactMessageTest.php
- tests/Feature/Member/MemberMessagesTest.php

FILES TO MODIFY:
- database/seeders/RolePermissionSeeder.php
- resources/views/components/member/navigation.blade.php
- resources/views/components/admin/sidebar.blade.php
- routes/web.php
- routes/admin.php

TASK 1: SCHEMA

Create migration: create_contact_messages_table.

Columns:
- id: primary key
- user_id: unsignedBigInteger, nullable, foreign key to users.id, null on delete, indexed
- name: string, required
- email: string, required
- subject: string, required
- message: text, required
- status: string, default 'new' (values: new, replied, closed), indexed
- reference_code: string, unique (e.g., 'MSG-000123')
- created_at, updated_at

Create migration: create_contact_replies_table.

Columns:
- id: primary key
- contact_message_id: unsignedBigInteger, foreign key to contact_messages.id, cascade on delete, indexed
- admin_id: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- reply: text, required
- created_at, updated_at

TASK 2: MODELS

Create app/Models/ContactMessage.php:
- Fillable: user_id, name, email, subject, message, status, reference_code.
- Relationships: user(), replies().
- Scope: scopeNew, scopeReplied.
- Generate reference_code on creation (e.g., 'MSG-' . str_pad(id, 6, '0', STR_PAD_LEFT)).

Create app/Models/ContactReply.php:
- Fillable: contact_message_id, admin_id, reply.
- Relationships: contactMessage(), admin().

TASK 3: PERMISSIONS

Modify RolePermissionSeeder.php.

Add permissions:
- contact-messages.view
- contact-messages.reply

Assign to: Super Administrator ONLY (per requirement: "super admin sees and replies").

TASK 4: PUBLIC CONTACT FORM

Create PublicSite\ContactController with create and store methods.

Routes:
- GET /contact → contact.create
- POST /contact → contact.store
- Middleware: throttle:contact (5 per hour per IP)

ContactRequest validation:
- name: required, string, 2-100
- email: required, email, max 255
- subject: required, string, 3-150
- message: required, string, 10-5000

Store logic:
1. Validate input.
2. If authenticated, set user_id and pre-fill name/email from profile.
3. Create contact message.
4. Generate reference_code.
5. Send NewContactMessageNotification to Super Admins via AdminNotifier.
6. Redirect back with success message including the reference_code.

Create resources/views/public/contact.blade.php:
- Use public layout.
- Form fields: name, email, subject, message.
- Pre-fill name/email if logged in.
- Display validation errors inline.
- Show success message with reference code after submission.
- Accessible labels and error messages.

TASK 5: ADMIN CONTACT INBOX

Create Admin\ContactMessageController with index and show methods.

Routes (in routes/admin.php):
- GET /admin/contact-messages → admin.contact-messages.index (permission: contact-messages.view)
- GET /admin/contact-messages/{id} → admin.contact-messages.show (permission: contact-messages.view)

Index page:
- List contact messages: subject, name, email, status badge, date.
- Filter by status (new, replied, closed).
- Default sort: newest first, or "new" status first.
- Pagination.
- Empty state if no messages.

Show page:
- Display the full message (name, email, subject, message, date).
- Display all existing replies (thread view).
- Reply form (textarea + submit).
- Status indicator.

TASK 6: ADMIN REPLY

Add reply method to Admin\ContactMessageController.

Route: POST /admin/contact-messages/{id}/reply (permission: contact-messages.reply)

ReplyContactRequest validation:
- reply: required, string, 10-5000

Reply logic:
1. Validate input.
2. Create contact_reply with admin_id = current user.
3. Update contact_message status to 'replied'.
4. Notify the sender:
   - If user_id is set (member): send ContactMessageRepliedNotification (in-app + email).
   - If user_id is null (guest): send email to the guest's email address (via ContactMessageRepliedNotification routed to an ad-hoc notifiable, or via Mail directly).
5. Audit log: contact-messages.replied.
6. Redirect back with success message.

TASK 7: MEMBER MESSAGES PAGE

Create Member\MessageController with index method.

Route: GET /account/messages → account.messages (middleware: auth, active.user, member)

Logic:
- Fetch the member's contact messages (where user_id = current user).
- Include replies.
- Order by created_at desc.

Create resources/views/member/messages.blade.php:
- Use member layout.
- List the member's contact messages with status.
- Expandable/threaded view showing replies.
- Empty state if no messages.
- Link to submit a new message (/contact).

TASK 8: NAVIGATION UPDATES

Modify resources/views/components/member/navigation.blade.php:
- Add "Messages" item → account.messages.

Modify resources/views/components/admin/sidebar.blade.php:
- Add "Contact Messages" item under "System" group.
- Icon: Envelope or Chat.
- Badge: count of "new" status messages.
- Only visible to users with contact-messages.view permission.

TASK 9: ROUTES

Add to routes/web.php:
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::middleware(['auth', 'active.user', 'member'])->group(function () {
    Route::get('/account/messages', [MessageController::class, 'index'])->name('account.messages');
});

Add to routes/admin.php:
Route::middleware(['permission:contact-messages.view'])->prefix('contact-messages')->name('contact-messages.')->group(function () {
    Route::get('/', [ContactMessageController::class, 'index'])->name('index');
    Route::get('/{id}', [ContactMessageController::class, 'show'])->name('show');
    Route::post('/{id}/reply', [ContactMessageController::class, 'reply'])->middleware('permission:contact-messages.reply')->name('reply');
});

TASK 10: FEATURE TESTS

Create tests/Feature/PublicSite/ContactFormTest.php:
- Test contact page renders.
- Test submitting a valid message stores it with a reference code.
- Test validation rejects missing fields.
- Test authenticated user's name/email are pre-filled and user_id is set.
- Test rate limiting (6th submission within an hour is blocked).
- Test new message notifies super admins.

Create tests/Feature/Admin/ContactMessageTest.php:
- Test super admin can view the contact inbox.
- Test non-super-admin cannot view the contact inbox.
- Test super admin can view a message detail.
- Test super admin can reply to a message.
- Test reply updates status to 'replied'.
- Test reply notifies the member sender.
- Test reply requires valid input.

Create tests/Feature/Member/MemberMessagesTest.php:
- Test member can view their messages page.
- Test member sees their own messages and replies.
- Test member does not see other members' messages.
- Test guest cannot access /account/messages.

SECURITY REQUIREMENTS:
- Contact form is rate limited to prevent spam.
- Only super admins can view/reply to contact messages.
- Members can only see their own messages.
- Guest email is stored but not exposed publicly.
- Audit log all replies.
- Sanitize message content before rendering.

DEFINITION OF DONE:
- contact_messages and contact_replies tables exist.
- Public contact form works with rate limiting and reference codes.
- Admin inbox lists and filters contact messages.
- Super admin can reply to messages.
- Members see their message history in /account/messages.
- New messages notify super admins.
- Replies notify the sender (in-app for members, email for guests).
- All feature tests pass.