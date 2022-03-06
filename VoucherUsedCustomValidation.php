<?php


use Illuminate\Contracts\Validation\Rule;

class VoucherUsedCustomValidationRule implements Rule
{
    private $voucherCode;

    /**
     * Create a new rule instance.
     *
     * @param $voucherCode
     */
    public function __construct($voucherCode)
    {
        $this->voucherCode = $voucherCode;
    }

    /**
     * Determine if the validation rule passes.
     * Check if the voucher code is valid based on the given user.
     *
     * @param string $attribute
     * @param array  $values
     *
     * @return bool
     */
    public function passes($attribute, $values): bool
    {
        $voucher = DB::table('vouchers')->where('code', $this->voucherCode)->first();

        // If unlimited used  for this voucher. (Check single user redemption limit)
        if ($voucher->usage_limit == -1 || $voucher->user_usage_limit == -1) {
            return true;
        }

        // check if limited uses of this voucher per user (Check overall redemption limit)

        $userId =  DB::table('users')::where('id', auth()->id())->first()['id'];

        $count_of_use = DB::table('voucher_users')->where('user_id', $userId)->where('voucher_id', '=', $voucher->id)->count();

        if ($count_of_use <= $voucher->user_usage_limit) {
            return true;
        }

        // return false if not valid conditions
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Voucher code already used';
    }
}
