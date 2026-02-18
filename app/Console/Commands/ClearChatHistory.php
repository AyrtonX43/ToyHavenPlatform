<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearChatHistory extends Command
{
    protected $signature = 'chat:clear-history {--force : Skip confirmation}';

    protected $description = 'Delete all chat messages and attachments from the database';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will permanently delete ALL chat messages and attachments. Continue?')) {
            return 0;
        }

        $attachmentCount = DB::table('message_attachments')->count();
        $messageCount = DB::table('messages')->count();

        // Delete attachment files from storage
        $paths = DB::table('message_attachments')->pluck('file_path');
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Must delete attachments first due to foreign key
        DB::table('message_attachments')->delete();
        DB::table('messages')->delete();

        // Clear last_message_at on conversations
        DB::table('conversations')->update(['last_message_at' => null]);

        $this->info("Deleted {$messageCount} messages and {$attachmentCount} attachments.");

        return 0;
    }
}
