<?php

namespace App\Notifications;

use App\Models\Assessment;
use App\Models\ExamAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultsReleasedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Assessment $assessment,
        protected ExamAttempt $attempt,
    ) {}

    /**
     * In-app only by design; the release moment is a site-wide event.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'results_released',
            'assessment_id' => $this->assessment->id,
            'message' => "Your {$this->assessment->title} result is live! View your score, rank and certificate.",
            'url' => route('exams.result', $this->attempt),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Results released: '.$this->assessment->title)
            ->line("Your {$this->assessment->title} result is live!")
            ->action('View your result', route('exams.result', $this->attempt));
    }
}
