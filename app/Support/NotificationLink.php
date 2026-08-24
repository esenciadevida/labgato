<?php

namespace App\Support;

use App\Models\User;

class NotificationLink
{
    /**
     * Resolve the action link and label for a stored notification.
     *
     * Handles both payload key generations ("url" and "action_url") and
     * repairs legacy contact-message payloads that pointed at /admin, a
     * route which does not exist.
     *
     * @return array{href: ?string, label: ?string, type: string}
     */
    public static function resolve(object $notification): array
    {
        $data = (array) ($notification->data ?? []);

        $href = $data['url'] ?? $data['action_url'] ?? null;
        $label = $data['action_text'] ?? ($href !== null ? 'View' : null);

        if ($href !== null && rtrim($href, '/') === rtrim(url('/admin'), '/')) {
            if (self::viewerCanReadMessages()) {
                return [
                    'href' => route('admin.contact-messages.index'),
                    'label' => 'Read',
                    'type' => self::type($notification),
                ];
            }

            return ['href' => null, 'label' => null, 'type' => self::type($notification)];
        }

        return ['href' => $href, 'label' => $label, 'type' => self::type($notification)];
    }

    /**
     * Payload type, inferred from the notification class when absent so
     * older rows still get the right icon in the bell panel.
     */
    public static function type(object $notification): string
    {
        $stored = data_get($notification->data ?? [], 'type');

        if ($stored !== null) {
            return (string) $stored;
        }

        $base = class_basename($notification->type ?? '');

        return match (true) {
            str_contains($base, 'ContactMessage') => 'contact_message',
            str_contains($base, 'ContributionApproved') => 'contribution_approved',
            str_contains($base, 'ContributionRejected') => 'contribution_rejected',
            str_contains($base, 'MemberRegistered') => 'member_registered',
            str_contains($base, 'ContributionSubmitted') => 'contribution_submitted',
            str_contains($base, 'ResultsReleased') => 'results_released',
            default => 'default',
        };
    }

    private static function viewerCanReadMessages(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ($user->isSuperAdministrator() || $user->hasPermission('contact-messages.view'));
    }
}
