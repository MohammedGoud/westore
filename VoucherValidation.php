<?php 

function voucherValidation($request)
    {

        $accessToken = $request->header('x-api-key');
        $data = json_decode($request->getContent());

        if ($accessToken != "" && isset($data->voucher_code) && $data->voucher_code != "" && isset($data->booking_amount) && $data->booking_amount > 0) {

            $response = Users::where('access_token', $accessToken)->get();
            if (count($response) > 0) {
                $userId = $response[0]->id;

                $voucherValid = DB::table('vouchers')->where('code', '=', $data->voucher_code)->where('status', '=', 1)->where('start_date', '<', Carbon::now())->where('end_date', '>', Carbon::now())->get();
                if (count($voucherValid) > 0) {
                    $voucherId = $voucherValid[0]->id;
                    $alreadyUsed = DB::table('voucher_users')->where('user_id', '=', $userId)->where('voucher_id', '=', $voucherId)->get();
                    if (count($alreadyUsed) > 0) {
                        $responseArray = [
                            "status" => false,
                            "status_code" => 0,
                            "msg" => "Voucher Code already used",
                            "updated_amount" => "",
                            "percentage_amount" => "",
                        ];
                        return response()->json($responseArray);
                    } else {
                        $num = round($data->booking_amount);
                        $formattedNum = str_replace(',', '', $num);

                        $percenntageAmount = "";
                        if ($voucherValid[0]->type == 1) {
                            $totalAmount = $formattedNum - $voucherValid[0]->value;

                        } else if ($voucherValid[0]->type == 2) {
                            $percenntageAmount = round($formattedNum * $voucherValid[0]->value / 100);
                            $totalAmount = $formattedNum - round($formattedNum * $voucherValid[0]->value / 100);
                        }

                        $responseArray = [
                            "status" => true,
                            "status_code" => 1,
                            "msg" => "Voucher Code Used Successfully",
                            "updated_amount" => "$totalAmount",
                            "percentage_amount" => "$percenntageAmount",
                        ];
                        return response()->json($responseArray);
                    }
                } else {

                    $responseArray = [
                        "status" => false,
                        "status_code" => 0,
                        "msg" => "Invalid Voucher Code",
                        "voucher_detail" => null,
                        "updated_booking_amount" => "",
                        "percentage_amount" => "",
                    ];

                    return response()->json($responseArray);
                }
            } else {
                $responseArray = [
                    "status" => false,
                    "status_code" => 401,
                    "msg" => "Unauthorized access",
                ];

                return response()->json($responseArray);
            }
        } else {
            $responseArray = [
                "status" => false,
                "status_code" => 0,
                "msg" => "Please provide all the required information",
            ];

            return response()->json($responseArray);
        }
    }
