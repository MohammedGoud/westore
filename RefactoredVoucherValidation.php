<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

function voucherValidation($request)
{
    // if there are validation errors it return array of validation, 
    // else return array of json content data.
    $data = $this->CheckVoucherValidations($request);

    $voucher = DB::table('vouchers')->where('code', '=', $data->voucher_code)->first();

    $booking_amount = round($data->booking_amount);

    $formatted_booking_amount = str_replace(',', '', $booking_amount);

    $percentage_amount = '';

    if ($voucher->type == 1) {

        $total_amount = $formatted_booking_amount - $$voucher->value;

    } elseif ($voucher->type == 2) {
        
        $percentage_amount = round($formatted_booking_amount * $$voucher->value / 100);

        $total_amount = $formatted_booking_amount - round($formatted_booking_amount * $$voucher->value / 100);
    }

    $responseArray = [
        'status' => true,
        'status_code' => 1,
        'msg' => 'Voucher Code Used Successfully',
        'updated_amount' => $total_amount,
        'percentage_amount' => $percentage_amount,
    ];

    return response()->json($responseArray);
}


function CheckVoucherValidations($request)
{
    $validated = $request->validate([
        'accessToken' => 'required|exists:users,access_token',
        'voucher_code' => [
            'required',
            Rule::exists('vouchers', 'code')->where('status', 1)->where('start_date', '<', Carbon::now())->where('end_date', '>', Carbon::now()),
            // Custom Validation Laravel Rule to check Validity of Voucher.
            // - Check overall redemption limit.
            // - Check single user redemption limit.
            new VoucherUsedCustomValidationRule($request->voucher_code)
        ],

        'booking_amount' => 'required',
        'booking_duration' => [
            'sometimes',
            Rule::in([0, 1, 3]),
            // Closure function to validate booking_duration matching with voucher or not.
            function ($attribute, $value, $fail) {
                $voucher_valid_booking_duration = DB::table('vouchers')->
                select('id')->where(
                    [
                    'code' => $request->voucher_code,
                    'booking_durations'=> $value
                    ]
                )->first();

                if (! $voucher_valid_booking_duration) {
                    $fail('voucher_valid_booking_duration');
                }
            },
        ],
    ]);

    if ($validated->errors()) {
        $responseArray = [
            'status' => false,
            'status_code' => 0,
            'errors' => $validated->errors()
        ];

        return response()->json($responseArray);
    }

    return json_decode($request->getContent());
}
