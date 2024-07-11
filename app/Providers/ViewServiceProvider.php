<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $conversations = Conversation::whereHas('participants', function ($query) {
                    $query->where('user_id', Auth::id());
                })->get();

                $totalUnreadMessages = 0;
                foreach ($conversations as $conversation) {
                    $participant = $conversation->participants()->where('user_id', Auth::id())->first();
                    $totalUnreadMessages += $participant->new_messages_count;
                }

                $view->with('totalUnreadMessages', $totalUnreadMessages);
            } else {
                $view->with('totalUnreadMessages', 0);
            }
        });
    }
}
