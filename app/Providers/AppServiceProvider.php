<?php

namespace App\Providers;

use App\Models\School;
use App\Models\Setting;
use App\Models\Uniform;
use App\Observers\ChatObserver;
use App\Observers\SchoolObserver;
use App\Observers\ServiceChatbotInfoObserver;
use App\Observers\SettingObserver;
use App\Observers\UniformObserver;
use App\Services\AI\EmbeddingService;
use App\Services\AI\MediaProcessor;
use App\Services\AI\OpenAiChatService;
use App\Services\AI\QdrantService;
use App\Services\BusinessInfoService;
use App\Services\WhatsApp\ReceivedMessage;
use Illuminate\Support\ServiceProvider;
use App\Listeners\UserEventSubscriber;
use Illuminate\Support\Facades\Event;
use Sdkconsultoria\WhatsappCloudApi\Lib\Message\ReceivedMessage as SdkReceivedMessage;
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
        $this->app->singleton(BusinessInfoService::class, fn () => new BusinessInfoService());

        $this->app->singleton(SdkReceivedMessage::class, fn ($app) => new ReceivedMessage());
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
        Setting::observe(SettingObserver::class);
        \App\Models\ServiceChatbotInfo::observe(ServiceChatbotInfoObserver::class);
    }
}
