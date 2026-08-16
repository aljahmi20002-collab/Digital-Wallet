@extends('frontend.layouts.user.index')
@section('title', __('Edit Withdraw Account'))
@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('Edit Withdraw Account')"
            :subtitle="__('Update payout details and keep your withdrawal flow accurate.')"
            icon="fas fa-pen-to-square"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.withdraw.account.index') }}">
                <i class="fa-solid fa-receipt"></i> {{ __('My Accounts') }}
            </a>
        </x-user-feature-header>
        <div class="card-main">
            <form action="{{ route('user.withdraw.account.update', $withdrawAccount->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Account Name --}}
                <div class="row">
                    <x-form.field
                            type="text"
                            name="account_name"
                            label="{{ __('Account Name') }}"
                            placeholder="{{ __('Enter Account Name') }}"
                            :value="old('account_name', $withdrawAccount->name)"
                            :colClass="'col-md-6'"
                            :required="true"
                    />

                    @foreach(\App\Support\WithdrawFieldNormalizer::normalize($withdrawAccount->credentials) as $field)
                        <x-form.field
                                :type="$field['type']"
                                :name="'credentials['.$field['name'].']'"
                                :label="$field['label'] ?? ucfirst($field['name'])"
                                :placeholder="$field['placeholder'] ?? ucfirst($field['name'])"
                                :value="old('credentials.'.$field['name'], $field['value'] ?? null)"
                                :colClass="'col-md-6 single-input-inner style-border'"
                                :required="($field['validation'] ?? null) === 'required'"
                                :options="$field['options'] ?? []"
                        />
                    @endforeach

                </div>


                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary mt-3 w-100">{{ __('Update Account') }}</button>
            </form>
        </div>
    </div>
@endsection
