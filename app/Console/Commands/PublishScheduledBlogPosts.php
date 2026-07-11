<?php

namespace App\Console\Commands;

use App\BlogPost;
use Illuminate\Console\Command;

class PublishScheduledBlogPosts extends Command
{
    protected $signature = 'blog:publish-scheduled';

    protected $description = 'Publica artículos del blog programados cuya fecha ya ha llegado';

    public function handle(): int
    {
        $posts = BlogPost::query()
            ->where('status', BlogPost::STATUS_SCHEDULED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($posts as $post) {
            $post->status = BlogPost::STATUS_PUBLISHED;
            $post->save();
            $count++;
        }

        $this->info("Publicados {$count} artículo(s) programado(s).");

        return self::SUCCESS;
    }
}
