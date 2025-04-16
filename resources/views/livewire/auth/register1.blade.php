<?php

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {

    public string $f_name_fa = '';
    public string $l_name_fa = '';
    public string $n_code = '';
    public string $mobile = '';
    public string $fingerprint = '';
    public ?int $cooldown = null;

    public function mount()
    {
        $this->updateCooldown();
    }

    public function check_data()
    {
        $cooldownKey = 'fp_sms_cooldown|' . $this->fingerprint;
        $limitKey = 'fp_sms_total|' . $this->fingerprint;

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $this->updateCooldown();
            $this->addError('phone', 'لطفاً ' . $this->cooldown . ' ثانیه صبر کنید.');
            return;
        }

        if (RateLimiter::attempts($limitKey) >= 5) {
            $this->addError('phone', 'سقف ارسال پیامک برای این دستگاه پر شده است.');
            return;
        }

        RateLimiter::hit($cooldownKey, 120); // ۲ دقیقه
        RateLimiter::hit($limitKey, 3600 * 24); // مثلاً یک روز

        $this->updateCooldown();


        $this->modal('mobile_verify')->show();

    }

    public function updateCooldown()
    {
        $this->cooldown = RateLimiter::availableIn('fp_sms_cooldown|' . $this->fingerprint);
    }


}; ?>
<div class="flex flex-col gap-6">

    @error('phone')
    <div class="text-red-500">{{ $message }}</div>
    @enderror

    <input type="text" wire:model="fingerprint" placeholder="Fingerprint" class="border p-1 rounded">

    <button wire:click="sendVerificationCode"
            @if($cooldown > 0) disabled @endif
            class="bg-blue-500 text-white px-4 py-2 rounded">
        ارسال پیامک
    </button>

    @if ($cooldown > 0)
        <div wire:poll.1s="updateCooldown" class="mt-2 text-sm text-gray-600">
            لطفاً {{ $cooldown }} ثانیه صبر کنید تا بتوانید دوباره پیامک ارسال کنید.
        </div>
    @endif


    <x-auth-header :title="__('ایجاد حساب')" :description="__('اطلاعات شخصی را جهت ثبت نام وارد کنید.')"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="check_data" class="flex flex-col gap-6" autocomplete="off">
        <!-- First Name -->
        <flux:input
            class:input="text-center"
            wire:model="f_name_fa"
            :label="__('نام')"
            type="text"
            required
            autofocus
            :placeholder="__('نام (فارسی)')"
        />

        <!-- Last Name -->
        <flux:input
            class:input="text-center"
            wire:model="l_name_fa"
            :label="__('نام خانوادگی')"
            type="text"
            required
            :placeholder="__('نام خانوادگی (فارسی)')"
        />

        <!-- National Code -->
        <flux:input
            class:input="text-center"
            wire:model="n_code"
            :label="__('کدملی')"
            type="text"
            required
            :placeholder="__('کد ملی')"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('ثبت نام') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('ثبت نام کرده اید؟') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('وارد شوید.') }}</flux:link>
    </div>

    <flux:modal name="mobile_verify" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Update profile</flux:heading>
                <flux:text class="mt-2">Make changes to your personal details.</flux:text>
            </div>
            <flux:input label="Name" placeholder="Your name"/>
            <flux:input label="Date of birth" type="date"/>
            <div class="flex">
                <flux:spacer/>
                <flux:button type="submit" variant="primary">Save changes</flux:button>
            </div>
        </div>
    </flux:modal>
</div>


@script
<script>
    let fingerprintData;
    let fingerprint;

    function gfpd() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '16px Arial';
        ctx.fillStyle = '#f60';
        ctx.fillRect(100, 10, 100, 100);
        ctx.fillStyle = '#069';
        ctx.fillText('I-Tech!', 10, 10);
        const canvasData = canvas.toDataURL();
        fingerprintData = [
            navigator.userAgent,
            navigator.language,
            screen.width,
            screen.height,
            screen.colorDepth,
            navigator.hardwareConcurrency,
            new Date().getTimezoneOffset(),
            canvasData,
        ].join('_');
    }

    gfpd();

    function simpleHash() {
        let hash = 0;
        for (let i = 0; i < fingerprintData.length; i++) {
            const char = fingerprintData.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash |= 0; // Convert to 32bit integer
        }
        fingerprint = Math.abs(hash).toString();
    }

    simpleHash();
    $wire.$set('fingerprint', fingerprint);
</script>
@endscript
