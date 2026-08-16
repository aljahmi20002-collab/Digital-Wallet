<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UpdateNotificationPreferenceRequest;
use App\Models\NotificationPreference;
use App\Support\NotificationTuneLibrary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user            = auth()->user();
        $notifications   = $user->notifications()->latest()->paginate(10);
        $preferenceState = $user->notificationPreferenceState();
        $tuneOptions     = NotificationTuneLibrary::tunes();
        $noteOptions     = NotificationTuneLibrary::noteOptions();
        $defaultTune     = NotificationTuneLibrary::resolve(NotificationTuneLibrary::defaultKey());

        return view('frontend.user.setting.notification', compact(
            'notifications',
            'preferenceState',
            'tuneOptions',
            'noteOptions',
            'defaultTune'
        ));
    }

    public function recent(): string
    {
        $notifications = auth()->user()->getRecentNotifications();

        return view('frontend.layouts.user.partials._notifications', compact('notifications'))->render();
    }

    public function updatePreference(UpdateNotificationPreferenceRequest $request): RedirectResponse
    {
        NotificationPreference::updateFor($request->user(), $request->preferenceData());

        notifyEvs('success', __('Notification preferences updated successfully.'));

        return redirect()->back();
    }

    public function markAsRead($id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }
}
