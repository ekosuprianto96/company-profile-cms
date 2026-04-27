<?php

return [
    'otp_length' => (int) env('MOBILE_AUTH_OTP_LENGTH', 6),
    'otp_expire_minutes' => (int) env('MOBILE_AUTH_OTP_EXPIRE_MINUTES', 10),
    'otp_max_attempts' => (int) env('MOBILE_AUTH_OTP_MAX_ATTEMPTS', 5),
    'token_expiration_days' => (int) env('MOBILE_AUTH_TOKEN_EXPIRATION_DAYS', 30),
];
