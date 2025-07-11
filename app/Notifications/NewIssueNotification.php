<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Issue;

//notif
class NewIssueNotification extends Notification
{
    use Queueable;

    public function __construct(public Issue $issue) {}

    public function via(object $notifiable): array
    {
        return ['database']; // Store in DB so it appears in Filament's notification panel
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Issue Reported',
            'message' => "{$this->issue->tenant->tenant_name} reported: {$this->issue->title}",
            //'url' => route('filament.admin.resources.issues.view', ['record' => $this->issue->id]),
            'url' => route('filament.admin.resources.issues.edit', ['record' => $this->issue->id])

        ];
    }
}

// class NewIssueNotification extends Notification
// {
//     use Queueable;

//     /**
//      * Create a new notification instance.
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Get the notification's delivery channels.
//      *
//      * @return array<int, string>
//      */
//     public function via(object $notifiable): array
//     {
//         return ['mail'];
//     }

//     /**
//      * Get the mail representation of the notification.
//      */
//     public function toMail(object $notifiable): MailMessage
//     {
//         return (new MailMessage)
//             ->line('The introduction to the notification.')
//             ->action('Notification Action', url('/'))
//             ->line('Thank you for using our application!');
//     }

//     /**
//      * Get the array representation of the notification.
//      *
//      * @return array<string, mixed>
//      */
//     public function toArray(object $notifiable): array
//     {
//         return [
//             //
//         ];
//     }
// }
