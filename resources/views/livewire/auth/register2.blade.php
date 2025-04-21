<?php

use App\Rules\NCode;
use App\Services\ParsGreenService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {

    public string $f_name_fa = '';
    public string $l_name_fa = '';
    public string $n_code = '';
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

    public function check_data(): void
    {
        $this->validate();

        $ip = Request::ip();
        $otp = NumericOTP(6);

        $total_fp_sms_sent_key = 'total_fp_sms_sent|' . $this->fingerprint;
        $fp_sms_cool_down_key = 'fp_sms_cool_down|' . $this->fingerprint;
        $total_ip_sms_sent_key = 'total_ip_sms_sent|' . $ip;
        $registration_mobile_key = 'registration_mobile|' . $this->mobile;
        $registration_otp_key = 'registration_otp|' . $otp;


        if (RateLimiter::attempts($total_fp_sms_sent_key) >= 5) {
            $this->addError('total_fp_sms_sent', 'سقف ارسال پیامک برای این مرورگر پرشده است.');
            return;
        }

        if (RateLimiter::attempts($total_ip_sms_sent_key) >= 20) {
            $this->addError('total_ip_sms_sent', 'سقف ارسال پیامک برای این آی پی پرشده است.');
            return;
        }

        $parsGreenService = new ParsGreenService();

        if ($this->cooldown > 0){
            $this->updateCooldown();
        }
        else{
            //      $response = $parsGreenService->sendOtp($this->mobile, $otp);
            $response = true;

        }


        if ($response) {
            $this->modal('mobile_verify')->show();
            RateLimiter::hit($total_fp_sms_sent_key, 3600);  // کلید تعداد ارسال تا 1 ساعت اعتبار دارد.
            RateLimiter::hit($total_ip_sms_sent_key, 3600 * 24); // 1 روز
            RateLimiter::hit($fp_sms_cool_down_key, 90); // 90 ثانیه
            RateLimiter::hit($registration_mobile_key, 90);
            RateLimiter::hit($registration_otp_key, 90);

        } else {
            $this->addError('sms_send_problem', 'مشکلی در ارسال پیامک پیش آمده است. بعد از لحضاتی مجدد تلاش کنید.');
            return;
        }


        $this->modal('mobile_verify')->show();
    }

    public function register()
    {

    }


    public function sendOtp(): void
    {
        $this->validate(['mobile' => 'required|starts_with:09|digits:11']);


        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $this->updateCooldown();
            $this->addError('phone', 'لطفاً ' . $this->cooldown . ' ثانیه صبر کنید.');
            return;
        }


        $this->updateCooldown();
    }

    public function updateCooldown(): void
    {
        $this->cooldown = RateLimiter::availableIn('fp_sms_cooldown|' . $this->fingerprint);
    }


}; ?>
<div class="flex flex-col gap-6">

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

    <!--------- Mobile Verification Modal --------->
    <flux:modal name="mobile_verify" class="md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('تایید شماره موبایل')}}</flux:heading>
                <flux:text
                    class="mt-2">{{__('پیامک دریافتی از شماره')}} <span class="font-bold">{{$mobile}}</span> {{__('را وارد نمایید.')}}</flux:text>
            </div>

            <!-- OTP -->
            <flux:input
                class:input="text-center"
                wire:model="otp"
                :label="__('کدپیامکی')"
                type="text"
                required
                autofocus
                :placeholder="__('کد پیامکی')"
            />

            <div class="flex">
                <flux:spacer/>
                <flux:button type="submit" variant="primary">{{__('تکمیل ثبت نام')}}</flux:button>
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
