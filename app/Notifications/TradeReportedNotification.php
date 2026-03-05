<?php

namespace App\Notifications;

use App\Models\ConversationReport;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeReportedNotification extends Notification
{
    public function __construct(
        public ConversationReport $report,
        public ?int $offenceCount = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $suspended = $this->offenceCount !== null;
        $msg = (new MailMessage)->subject('Trade Report - ToyHaven');
        if ($suspended) {
            $duration = $this->offenceCount === 1 ? '5 days' : ($this->offenceCount === 2 ? '30 days' : 'permanently');
            $msg->line('Someone reported you in a trade conversation. After review, your trade access has been suspended for ' . $duration . '.')
                ->line('Please ensure you follow our trade guidelines. Repeated violations may result in permanent ban.');
        } else {
            $msg->line('Someone reported you in a trade conversation. Admin will review the report.')
                ->line('Please ensure you follow our trade guidelines. Repeated violations may result in suspension or ban.');
        }
        return $msg;
    }

    public function toArray(object $notifiable): array
    {
        $suspended = $this->offenceCount !== null;
        $message = $suspended
            ? 'You were reported and your trade access has been suspended.'
            : 'Someone reported you in a trade. Admin will review. Please follow our guidelines to avoid suspension or ban.';
        return [
            'type' => 'trade_reported',
            'title' => 'Trade Report',
            'message' => $message,
            'report_id' => $this->report->id,
        ];
    }
}
