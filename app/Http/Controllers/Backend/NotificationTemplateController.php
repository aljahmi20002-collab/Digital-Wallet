<?php

namespace App\Http\Controllers\Backend;

use App\Enums\NotificationChannelType;
use App\Enums\UserType;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateChannel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NotificationTemplateController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index'              => 'notification-template-list',
            'edit|updateChannel' => 'notification-template-manage',
        ];
    }

    public function index(Request $request)
    {
        $search   = trim((string) $request->input('search'));
        $userType = trim((string) $request->input('user_type'));
        $channel  = trim((string) $request->input('channel'));
        $status   = trim((string) $request->input('status'));
        $sort     = $request->input('sort') === 'oldest' ? 'oldest' : 'newest';

        $query = NotificationTemplate::query()
            ->with('channels')
            ->withCount([
                'channels as active_channels_count'   => fn (Builder $builder) => $builder->where('is_active', true),
                'channels as inactive_channels_count' => fn (Builder $builder) => $builder->where('is_active', false),
            ]);

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('info', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%")
                    ->orWhere('variables', 'like', "%{$search}%");
            });
        }

        if (UserType::tryFrom($userType) instanceof UserType) {
            $query->where('user_type', $userType);
        }

        if (NotificationChannelType::tryFrom($channel) instanceof NotificationChannelType) {
            $query->whereHas('channels', function (Builder $builder) use ($channel) {
                $builder->where('channel', $channel);
            });
        }

        if ($status === 'active') {
            $query->whereHas('channels', function (Builder $builder) {
                $builder->where('is_active', true);
            });
        }

        if ($status === 'inactive') {
            $query->whereDoesntHave('channels', function (Builder $builder) {
                $builder->where('is_active', true);
            });
        }

        $query->orderBy('updated_at', $sort === 'oldest' ? 'asc' : 'desc');

        $notifyTemplates = $query->paginate(10)->withQueryString();

        $templateStats = [
            'total'  => NotificationTemplate::count(),
            'active' => NotificationTemplate::whereHas('channels', function (Builder $builder) {
                $builder->where('is_active', true);
            })->count(),
            'inactive' => NotificationTemplate::whereDoesntHave('channels', function (Builder $builder) {
                $builder->where('is_active', true);
            })->count(),
            'channels' => NotificationTemplateChannel::count(),
        ];

        $userTypeCounts = [
            ''                     => NotificationTemplate::count(),
            UserType::USER->value  => NotificationTemplate::where('user_type', UserType::USER->value)->count(),
            UserType::ADMIN->value => NotificationTemplate::where('user_type', UserType::ADMIN->value)->count(),
        ];

        $filterOptions = [
            'userTypes' => UserType::options(),
            'channels'  => NotificationChannelType::options(),
            'statuses'  => [
                'active'   => __('Active Templates'),
                'inactive' => __('Inactive Templates'),
            ],
            'sorts' => [
                'newest' => __('Newest First'),
                'oldest' => __('Oldest First'),
            ],
        ];

        $activeFilterCount = collect([
            $search   !== '' ? 'search' : null,
            $userType !== '' ? 'user_type' : null,
            $channel  !== '' ? 'channel' : null,
            $status   !== '' ? 'status' : null,
            $sort     !== 'newest' ? 'sort' : null,
        ])->filter()->count();

        return view('backend.notifications.template.index', compact(
            'notifyTemplates',
            'templateStats',
            'userTypeCounts',
            'filterOptions',
            'activeFilterCount'
        ));
    }

    public function edit(NotificationTemplate $template)
    {
        $template->load('channels');

        return view('backend.notifications.template.edit', compact('template'));
    }

    public function updateChannel(Request $request, NotificationTemplate $template, NotificationTemplateChannel $channel)
    {

        $request->validate([
            'title'   => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        if ($channel->template_id != $template->id) {
            abort(403);
        }

        $channel->update([
            'title'     => $request->input('title'),
            'message'   => $request->input('message'),
            'is_active' => $request->has('is_active'),
        ]);

        notifyEvs('success', 'Channel template updated successfully.');

        return redirect()->route('admin.notifications.template.index');
    }
}
