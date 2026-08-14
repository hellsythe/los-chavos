<?php

namespace App\Providers;

use App\Models\School;
use App\Models\Uniform;
use App\Observers\ChatObserver;
use App\Observers\SchoolObserver;
use App\Observers\UniformObserver;
use App\Services\AI\EmbeddingService;
use App\Services\AI\MediaProcessor;
use App\Services\AI\OpenAiChatService;
use App\Services\AI\QdrantService;
use Illuminate\Support\ServiceProvider;
use App\Listeners\UserEventSubscriber;
use Illuminate\Support\Facades\Event;
use Sdkconsultoria\WhatsappCloudApi\Models\Chat;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EmbeddingService::class, fn () => EmbeddingService::make());
        $this->app->singleton(OpenAiChatService::class, fn () => OpenAiChatService::make());
        $this->app->singleton(MediaProcessor::class, fn () => MediaProcessor::make());
        $this->app->singleton(QdrantService::class, fn () => QdrantService::make());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // if (app()->environment('production')) {
        //     \URL::forceScheme('https');
        // }
        Event::subscribe(UserEventSubscriber::class);

        School::observe(SchoolObserver::class);
        Uniform::observe(UniformObserver::class);
        Chat::observe(ChatObserver::class);
    }
}
