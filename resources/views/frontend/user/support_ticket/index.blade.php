@extends('frontend.layouts.user.index')
@section('title', __('Support Tickets'))
@section('content')
    <div class="card single-form-card">
        <x-user-feature-header
            :title="__('Support Tickets')"
            :subtitle="__('Track ongoing conversations and create new help requests when needed.')"
            icon="fas fa-headset"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.support-ticket.create') }}">
                <i class="fa-solid fa-ticket-alt"></i> {{ __('Create Ticket') }}
            </a>
        </x-user-feature-header>


        {{-- Card Body --}}
        <div class="card-body">
            {{-- Tickets List --}}
            <div class="ticket-list">
                @forelse($tickets as $ticket)
                    {{-- Ticket Item --}}
                    <div class="ticket-item d-flex align-items-center p-2 rounded mb-2 bg-light">
                        {{-- Ticket Details --}}
                        <div class="ticket-details flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    {{-- Title --}}
                                    <a href="{{ route('user.support-ticket.show', $ticket->id) }}"
                                       class="text-dark fw-bold mb-1">
                                        {{ $ticket->title }}
                                        <span class="badge bg-{{ $ticket->priority->badgeColor() }} text-white small">
                                             <i class="fa-solid fa-bolt"></i> {{ $ticket->priority->label() }}
                                        </span>
                                    </a>
                                    {{-- Ticket Metadata --}}
                                    <div class="text-muted small">
                                        <span>{{ __('UUID:') }} {{ $ticket->uuid }}</span>
                                        @if($ticket->isReplied())
                                            <span class="badge bg-success ms-2">
                                                        <i class="fa-light fa-comment"></i>
                                                        {{ __('Answered') }}
                                                    </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status & Priority --}}
                                <div class="text-end">
                                            <span class="badge bg-{{ $ticket->status->badgeColor() }} text-white small mt-1">
                                                {{ $ticket->status->label() }}
                                            </span>
                                    <div class="text-muted small mt-1">
                                        {{ $ticket->created_at->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-user-not-found
                        :title="__('No tickets found')"
                        :message="__('Create a support ticket when you need help from the team.')"
                        :eyebrow="__('Support desk ready')"
                        icon="fa-headset"
                        :action-url="route('user.support-ticket.create')"
                        :action-label="__('Create Ticket')"
                        action-icon="fa-ticket-alt"
                    />
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($tickets->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
