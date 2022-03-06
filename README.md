# PHP Code Challenge

You have a function that simply do a voucher validation. This function should return a json response as it will be used as an api endpoint. Currently this function is limited and we want to expand it to make our voucher validation smart. 

Your task is to do the following
- Clean the current code of the function to make it more optimized and secured
- Update the validation to have the following checks
  - Check date validity of the coupon
  - Check overall redemption limit
  - Check single user redemption limit
  - Check the booking duration
- Commit your changes

Here are the request details that will help you understand what will be sent to your function

The request will have the following in the body

| Field Name            | Required/Optional    |
| --------------------- | ---------------------|
| **voucher_code**      | **Required**         |
| **booking_amount**    | **Required**         |
| **booking_duration**  | **Optional**         |

The request will have x-api-key in the header which is the access token of the user that you should use to get the details of the user.


Here is how the vouchers table looks like 

- id
- code
- start_date
- end_date
- type 
  - 0: means fixed amount
  - 1: means percentage
- value 
- usage_limit
  - -1: means unlimited   
- user_usage_limit
  - -1: means unlimited 
- booking_durations
  - 0: means all duration
  - 1,3: means it is available for bookings that has duration 1 or 3 months

and here is how the voucher_users table looks like. This will help you track voucher redemption per user

  - id
  - voucher_id
  - user_id
  - date_used
