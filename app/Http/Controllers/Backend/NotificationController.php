<?php

namespace App\Http\Controllers\Backend;

use App\Enums\UserRole;
use App\Jobs\NotifyUsers;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|recent'                 => 'notification-list',
            'notifyUsers|sendNotification' => 'custom-notify-users',
        ];
    }

    /**
     * Display paginated notifications.
     */
    public function index(Request $request): View
    {
        $filter = in_array($request->query('filter'), ['unread', 'read'], true)
            ? $request->query('filter')
            : 'all';

        $query = auth()->user()->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();

        return view('backend.notifications.index', compact('notifications', 'filter'));
    }

    /**
     * Return recent notifications for the dropdown.
     */
    public function recent(): string
    {
        $notifications = auth()->user()->getRecentNotifications();

        return view('backend.layouts.partials._notifications', compact('notifications'))->render();
    }

    public function notifyUsers(): View
    {
        return view('backend.notifications.notify_users');
    }

    public function sendNotification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'     => ['nullable'],
            'user_type'   => $request->filled('user_id') ? ['nullable'] : ['required'],
            'notify_type' => 'required|in:email,push',
            'title'       => $request->input('notify_type') === 'push'
                ? 'nullable|string|max:255'
                : 'required|string|max:255',
            'message'     => 'required|string',
            'schedule_at' => 'nullable|date|after_or_equal:now',
        ]);

        // Handle 'all' or convert string to array only for bulk user
        $userTypes = [];
        if (! $request->filled('user_id')) {
            $userTypes = $validated['user_type'] === 'all'
                ? UserRole::all()
                : (array) $validated['user_type'];

            $userTypes = collect($userTypes)
                ->map(fn ($value) => UserRole::tryFrom($value))
                ->filter()
                ->pluck('value')
                ->all();
        }

        // Apply timezone config and convert schedule time
        $timezone = setting('site_timezone', config('app.timezone', 'UTC'));

        $scheduleAt = isset($validated['schedule_at'])
            ? Carbon::parse($validated['schedule_at'], $timezone)->timezone($timezone)
            : now($timezone);

        $data = [
            'user_id'     => $validated['user_id'] ?? null,
            'user_types'  => $userTypes,
            'notify_type' => $validated['notify_type'],
            'title'       => $validated['title'] ?? null,
            'message'     => $validated['message'],
            'schedule_at' => $scheduleAt,
        ];

        dispatch(new NotifyUsers($data))->delay($scheduleAt);

        notifyEvs('success', 'Notification sent successfully.');

        return back();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        // Use each() to iterate over the collection and mark each as read.
        auth()->user()->unreadNotifications->each->markAsRead();

        return redirect()->back();
    }

    /**
     * Mark a single notification as read.
     *
     * @param string $id
     */
    public function markAsRead($id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back();
    }
}
