<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send scheduled reminders for upcoming appointments (demo)';

    public function handle(): int
    {
        $due = Reminder::where('sent', false)->where('send_at', '<=', now())->get();
        foreach ($due as $rem) {
            // Here you would integrate SMS/Email sending. We'll just mark as sent.
            $rem->update(['sent' => true]);
            $this->info("Marked reminder {$rem->id} as sent");
        }
        return 0;
    }
}
