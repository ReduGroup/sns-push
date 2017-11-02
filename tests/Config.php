<?php

namespace Tests;

class Config
{
    private static $data = [
                'account_id' => '01234567890',
                'access_key' => 'ACCESSKEY',
                'secret_key' => 'SECRET_KEY',
                'platform_applications' => [
                    'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                    'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                ],
            ];

    public static function data()
    {
        return static::$data;
    }

    public static function platformApplications()
    {
        return static::$data['platform_applications'];
    }

    public static function accountId()
    {
        return static::$data['account_id'];
    }

    public static function accessKey()
    {
        return static::$data['access_key'];
    }

    public static function secretKey()
    {
        return static::$data['secret_key'];
    }
}
