<?php

use App\Rules\NCode;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {

    public string $f_name_fa = '';
    public string $l_name_fa = '';
    public string $n_code = '';
    public $title = '';
    public string $mobile = '';
    public string $fingerprint = '';
    public ?int $cooldown = null;

    protected function rules(): array
    {
        return [
            'f_name_fa' => ['required', 'string', 'min:2'],
            'l_name_fa' => ['required', 'string', 'min:2'],
            'n_code' => ['required', 'digits:10', new NCode, Rule::unique('profiles', 'n_code')],
            'mobile' => ['required', 'starts_with:09', 'digits:11']
        ];
    }

    public function check_data()
    {
        $this->validate();

        $this->modal('mobile_verify')->show();
    }


    public function sendOtp(): void
    {
        $this->validate(['mobile' => 'required|starts_with:09|digits:11']);

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

        RateLimiter::hit($cooldownKey, 90); // 90 ثانیه
        RateLimiter::hit($limitKey, 3600 * 24); // مثلاً یک روز
        $this->updateCooldown();
    }

    public function updateCooldown(): void
    {
        $this->cooldown = RateLimiter::availableIn('fp_sms_cooldown|' . $this->fingerprint);
    }


}; ?>
<div class="flex flex-col gap-6">

    @error('phone')
    <div class="text-red-500">{{ $message }}</div>
    @enderror


    @if ($cooldown > 0)
        <div class="mt-2 text-sm text-gray-600">
            لطفاً {{ $cooldown }} ثانیه صبر کنید تا بتوانید دوباره پیامک ارسال کنید.
        </div>
    @endif


    <x-auth-header :title="__('ایجاد حساب')" :description="__('اطلاعات شخصی را جهت ثبت نام وارد کنید.')"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="check_data" class="flex flex-col gap-5" autocomplete="off">

        <!-- First Name -->
        <flux:field>
            <flux:label badge="الزامی">{{__('نام')}}</flux:label>
            <flux:input type="text" required autofocus class:input="text-center" wire:model="f_name_fa"/>
            <flux:error name="f_name_fa"/>
        </flux:field>

        <!-- Last Name -->
        <flux:field>
            <flux:label badge="الزامی">{{__('نام خانوادگی')}}</flux:label>
            <flux:input type="text" required class:input="text-center" wire:model="l_name_fa"/>
            <flux:error name="l_name_fa"/>
        </flux:field>

        <!-- National Code -->
        <flux:field>
            <flux:label badge="الزامی">{{__('کدملی')}}</flux:label>
            <flux:input type="text" required class:input="text-center" wire:model="n_code" style="direction:ltr"
                        maxlength="10"/>
            <flux:error name="n_code"/>
        </flux:field>

        <!-- Mobile -->
        <flux:field>
            <flux:label badge="الزامی">{{__('شماره موبایل')}}</flux:label>
            <flux:input type="text" required class:input="text-center" wire:model="mobile" style="direction:ltr"
                        maxlength="11"/>
            <flux:error name="mobile"/>
        </flux:field>


        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full cursor-pointer">
                {{ __('ثبت نام') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('ثبت نام کرده اید؟') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('وارد شوید.') }}</flux:link>
    </div>

    <!-- Mobile Verification Modal -->
    <flux:modal name="mobile_verify" class="md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('تایید شماره موبایل')}}</flux:heading>
                <flux:text
                    class="mt-2">{{__('موبایل خود را وارد نموده و دکمه ارسال پیامک را بزنید.')}}</flux:text>
            </div>

            <form wire:submit="sendOtp" class="flex flex-col gap-6" autocomplete="off">

                <!-- Mobile -->
                <flux:input.group>
                    <flux:input
                        maxlength="11"
                        class:input="text-center"
                        wire:model="mobile"
                        type="text"
                        autofocus
                        placeholder="شماره موبایل"/>

                    @if($cooldown > 0)
                        <flux:button wire:poll.1s="updateCooldown" style="width:150px">
                            {{$cooldown}} {{__('ثانیه')}}
                        </flux:button>
                    @else
                        <flux:button type="submit" style="cursor:pointer;">
                            {{__('ارسال پیامک')}}
                        </flux:button>
                    @endif
                </flux:input.group>
            </form>

            <form wire:submit="register" class="flex flex-col gap-6" autocomplete="off">
                <!-- Mobile -->
                <flux:input.group style="direction:ltr">
                    @if($cooldown > 0)
                        <flux:button wire:poll.1s="updateCooldown">
                            <span style="direction:rtl">{{$cooldown}} {{__('ثانیه')}}</span>
                        </flux:button>
                    @else
                        <flux:button type="submit" style="cursor:pointer; width:150px">
                            {{__('تایید')}}
                        </flux:button>
                    @endif

                    <flux:input
                        maxlength="11"
                        class:input="text-center"
                        wire:model="otp"
                        type="text"
                        autofocus
                        placeholder="کد پیامکی"/>

                </flux:input.group>
            </form>

            <!-- OTP -->
            <flux:input
                class:input="text-center"
                wire:model="otp"
                :label="__('کدپیامکی')"
                type="text"
                required
                :placeholder="__('کد پیامکی')"
            />

            <div class="flex">
                <flux:spacer/>
                <flux:button type="submit" variant="primary">{{__('تایید')}}</flux:button>
            </div>
            </form>
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
