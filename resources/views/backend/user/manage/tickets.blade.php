@extends('backend.user.manage')
@section('user_manage_content')
    <div class="card-body pt-0">
        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table border user-table align-items-center">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Ticket Info') }}</th>
                        <th>{{ __('Opening Time') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="align-middle">
                            <td>
                                <div class="fw-bold">{{ $ticket->title }}</div>
                                <div class="text-muted small text-info">
                                    {{ ucwords($ticket->uuid) }}<span class="mx-2 badge bg-{{ $ticket->priority->badgeColor() }} ">{{ $ticket->priority->label() }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $ticket->created_at->format('Y-m-d H:i') }}</div>
                                <div class="text-muted small">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="fw-bold text-uppercase">
                                <span class="badge bg-{{ $ticket->status->badgeColor() }} ">{{ $ticket->status->label() }}</span>
                                @if($ticket->is_resolved)
                                    <span class="badge bg-success ms-1">{{ __('Resolved') }}</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                <a href="{{ route('admin.support-ticket.show', $ticket->id) }}" class="btn btn-primary">
                                    <x-icon name="chat" height="20"/>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin-not-found
                                    :title="__('No tickets found')"
                                    :message="__('This user has not opened any support tickets yet.')"
                                    icon="fa-headset"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
