labgato/
├── .env.example
├── config/
│   └── mail.php
├── database/
│   └── migrations/
│       └── xxxx_create_notifications_table.php
├── app/
│   ├── Notifications/
│   │   ├── MemberRegisteredNotification.php
│   │   ├── ContributionApprovedNotification.php
│   │   ├── ContributionRejectedNotification.php
│   │   ├── NewContributionSubmittedNotification.php
│   │   ├── NewContactMessageNotification.php
│   │   └── ContactMessageRepliedNotification.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── NotificationController.php
│   └── Support/
│       └── AdminNotifier.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── notifications/
│       │       └── bell.blade.php
│       └── vendor/
│           └── notifications/
│               └── email.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Notifications/
            ├── MemberNotificationTest.php
            └── AdminNotificationTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD31-NOTIFICATIONS

CONTEXT:
LabGato needs a mail/notification system built "on ground" — fully functional but not sending real emails yet (no domain). We use Laravel Notifications with a database channel (in-app, works now) and a mail channel (ready, uses log driver locally, activates with real SMTP later).

OBJECTIVE:
Set up SMTP configuration (log driver locally), create the notifications table, build notification classes and branded email templates, wire triggers into registration and contribution flows, and add an in-app notification bell.

EXISTING ARCHITECTURE:
- Single users table with user_type.
- Contribution workflow exists (Build 28).
- Member and admin areas exist.
- Hostinger shared hosting compatible (no Redis; database channel for notifications).

FILES TO INSPECT:
- app/Models/User.php
- app/Http/Controllers/Auth/RegisterController.php
- app/Http/Controllers/Admin/ContributionReviewController.php
- app/Http/Controllers/Member/ContributionController.php
- config/mail.php
- resources/views/components/public/header.blade.php
- resources/views/components/admin/header.blade.php

FILES TO CREATE:
- database/migrations/xxxx_create_notifications_table.php
- app/Notifications/MemberRegisteredNotification.php
- app/Notifications/ContributionApprovedNotification.php
- app/Notifications/ContributionRejectedNotification.php
- app/Notifications/NewContributionSubmittedNotification.php
- app/Notifications/NewContactMessageNotification.php
- app/Notifications/ContactMessageRepliedNotification.php
- app/Http/Controllers/NotificationController.php
- app/Support/AdminNotifier.php
- resources/views/components/notifications/bell.blade.php
- tests/Feature/Notifications/MemberNotificationTest.php
- tests/Feature/Notifications/AdminNotificationTest.php

FILES TO MODIFY:
- .env.example
- config/mail.php
- app/Http/Controllers/Auth/RegisterController.php
- app/Http/Controllers/Member/ContributionController.php
- app/Http/Controllers/Admin/ContributionReviewController.php
- resources/views/components/public/header.blade.php
- resources/views/components/admin/header.blade.php
- routes/web.php

TASK 1: MAIL CONFIGURATION

Update .env.example with mail settings (default to log driver for local dev):

MAIL_MAILER=log
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifications@labgato.local
MAIL_FROM_NAME="LabGato"

Verify config/mail.php reads these via env().

LOCAL NOTE: MAIL_MAILER=log writes emails to storage/logs/laravel.log so templates
can be verified without sending. When a domain + SMTP is available, change to
MAIL_MAILER=smtp and fill in host/username/password.

TASK 2: NOTIFICATIONS TABLE

Run: php artisan notifications:table
This generates the notifications table migration. Then run: php artisan migrate

The notifications table stores in-app (database channel) notifications.

TASK 3: NOTIFICATION CLASSES

Create the following notifications. Each uses channels: ['database', 'mail'].

The mail channel should only actually send when MAIL_MAILER is not 'log' or 'null'.
For now, with log driver, mail output goes to laravel.log.

1. MemberRegisteredNotification
   - Sent to: the new member.
   - Subject: "Welcome to LabGato"
   - Content: Welcome message, link to dashboard, link to submit first contribution.

2. ContributionApprovedNotification
   - Sent to: the contributor.
   - Subject: "Your contribution has been approved"
   - Content: Contribution title, link to the published public page.

3. ContributionRejectedNotification
   - Sent to: the contributor.
   - Subject: "Update on your contribution"
   - Content: Contribution title, rejection reason, link to revise and resubmit.

4. NewContributionSubmittedNotification
   - Sent to: admins (Super Admin + Content Manager).
   - Subject: "New contribution awaiting review"
   - Content: Contribution title, type, contributor name, link to review queue.

5. NewContactMessageNotification (prepared for Build 32)
   - Sent to: Super Admin.
   - Subject: "New contact message"

6. ContactMessageRepliedNotification (prepared for Build 32)
   - Sent to: the contact message sender.
   - Subject: "Re: Your message to LabGato"

Each notification implements:
- via($notifiable): return ['database', 'mail'].
- toMail($notifiable): return a MailMessage with subject, lines, and action button.
- toArray($notifiable): return data for the database channel (for in-app display).

TASK 4: BRANDED EMAIL TEMPLATES

Publish Laravel's notification mail views:
php artisan vendor:publish --tag=laravel-notifications

This creates resources/views/vendor/notifications/email.blade.php.

Customize email.blade.php with LabGato branding:
- Header: LabGato logo/name in clinical teal.
- Footer: "LabGato — Professional Laboratory Toolkit".
- Professional, clean styling matching the theme.
- Do not include gradients or heavy decoration.

TASK 5: ADMIN NOTIFIER HELPER

Create app/Support/AdminNotifier.php.

A helper service to notify all relevant admins:

class AdminNotifier
{
    // Notify all users with a specific permission (e.g., contributions.review)
    public static function notifyAdminsWithPermission(string $permission, $notification): void

    // Notify all Super Admins
    public static function notifySuperAdmins($notification): void
}

Implementation:
- Query users where user_type='admin', is_active=true.
- Filter by permission (using the Role/Permission relationship) or role name.
- Send the notification to each.

TASK 6: WIRE NOTIFICATION TRIGGERS

Modify RegisterController:
- After successful member registration, send MemberRegisteredNotification to the new member.

Modify Member\ContributionController (submit method):
- When a member submits a contribution for review, send NewContributionSubmittedNotification to admins via AdminNotifier.

Modify Admin\ContributionReviewController:
- On approve: send ContributionApprovedNotification to the contributor.
- On reject: send ContributionRejectedNotification to the contributor (include reason).

TASK 7: IN-APP NOTIFICATION BELL

Create resources/views/components/notifications/bell.blade.php.

An Alpine.js dropdown component:
- Bell icon with unread count badge.
- Click opens a dropdown listing recent notifications (last 10).
- Each notification shows: icon/type, message, time ago.
- "Mark all as read" button.
- "View all" link.
- Accessible (aria-expanded, role=menu).

Add the bell to:
- resources/views/components/public/header.blade.php (for authenticated members/admins).
- resources/views/components/admin/header.blade.php.

TASK 8: NOTIFICATION ROUTES & CONTROLLER

Create NotificationController with:
- index: List all notifications for the authenticated user.
- markAsRead: Mark a single notification as read.
- markAllAsRead: Mark all notifications as read.

Add routes to routes/web.php (under auth middleware):
- GET /notifications → index
- POST /notifications/{id}/read → markAsRead
- POST /notifications/read-all → markAllAsRead

Create resources/views/notifications/index.blade.php (full notification list page).

TASK 9: FEATURE TESTS

Create tests/Feature/Notifications/MemberNotificationTest.php:
- Test registration creates a welcome notification in the database.
- Test contribution approval creates a notification for the contributor.
- Test contribution rejection creates a notification with the reason.
- Test member can view their notifications.
- Test member can mark notifications as read.

Create tests/Feature/Notifications/AdminNotificationTest.php:
- Test submitting a contribution notifies admins with contributions.review permission.
- Test AdminNotifier notifies only active admins.
- Test admin can view their notifications.
- Test notification bell shows unread count.

Use Notification::fake() in tests to assert notifications were sent.

MAIL BEHAVIOR:
- With MAIL_MAILER=log, mail channel output goes to laravel.log (no real sending).
- Database channel always works (in-app notifications).
- When MAIL_MAILER is switched to smtp later, real emails send automatically.

SECURITY REQUIREMENTS:
- Users can only view their own notifications.
- Notification content must not leak sensitive data (e.g., rejection reason is fine, but not internal IDs).
- AdminNotifier only notifies active admin accounts.

DEFINITION OF DONE:
- Mail configured with log driver locally, SMTP-ready.
- Notifications table exists.
- All 6 notification classes created with mail + database channels.
- Email templates are branded and professional.
- Registration triggers welcome notification.
- Contribution submit/approve/reject trigger appropriate notifications.
- Admins are notified of new submissions.
- In-app notification bell works for members and admins.
- All feature tests pass.
- No real emails are sent (log driver), but system is ready to activate.