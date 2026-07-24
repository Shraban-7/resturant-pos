<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


if (!function_exists('apiResponse')) {
    function apiResponse(object|array $data, string|null $message = null, int $statusCode = 200,)
    {
        $response['status'] = true;

        if (isset($message)) $response['message'] = $message;
        if (!empty($data)) $response['data'] = $data;

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('successResponse')) {
    function successResponse(string $message, int $statusCode = 200)
    {
        $response['status'] = true;

        if (isset($message)) $response['message'] = $message;

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse(string $message, int $statusCode = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message ?? 'Something went wrong!',
        ], $statusCode);
    }
}
if (!function_exists('str_slug')) {
    function str_slug($title, $separator = '-')
    {
        return Str::slug($title, $separator);
    }
}

if (!function_exists('upload_file')) {
    function upload_file($file, $directory, $disk = 'public')
    {
        if (!Storage::disk($disk)->exists($directory)) {
            Storage::disk($disk)->makeDirectory($directory);
        }

        $fileName =  time() . rand(1, 9999) . '.' . $file->extension();
        $path = $directory . '/' . $fileName;
        Storage::disk($disk)->put($path, File::get($file));

        return $path;
    }
}

if (!function_exists('storage_url')) {
    function storage_url($file, $disk = 'public')
    {
        return Storage::disk($disk)->url($file);
    }
}

if (!function_exists('delete_file')) {
    function delete_file($file)
    {
        if (Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
        }
    }
}

if (!function_exists('formatFriendlyDate')) {
    function formatFriendlyDate($date = null)
    {
        if (!$date) {
            return null;
        }

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        if ($date->isToday()) {
            return 'Today at ' . $date->format('h:i A');
        } elseif ($date->isTomorrow()) {
            return 'Tomorrow at ' . $date->format('h:i A');
        } elseif ($date->isYesterday()) {
            return 'Yesterday at ' . $date->format('h:i A');
        } else {
            return $date->format('F j, Y \a\t h:i A');
        }
    }
}

if (!function_exists('generateUniqueCode')) {
    function generateUniqueCode($length = 6)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);

        $code = '';

        while (strlen($code) < $length) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code . $character;
        }

        return $code;
    }
}

if (!function_exists('isValidUsername')) {
    function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{6,40}$/', $username) === 1;
    }
}

if (!function_exists('currency')) {
    function currency($key='symbol')
    {
        $currency = array(
            'name' => 'BDT',
            'symbol' => '৳',
        );

        return $currency[$key];
    }
}
if (!function_exists('money')) {
    function money($amount, $currencyType = 'symbol')
    {
        $money = number_format($amount, 2);
        $decimal = explode('.', $money);
        if ($decimal[1] == '00') {
            $money = str_replace('.00', '', $money);
        }
        return currency($currencyType) . ' ' . $money;
    }
}


if(!function_exists('is_seller')) {
    function is_seller() : bool
    {
        if (!Auth::user()) {
            return false;
        }
        
        return Auth::user()->role === 'seller';
    }
}
if(!function_exists('is_supplier')) {
    function is_supplier() : bool
    {
        if (!Auth::user()) {
            return false;
        }
        return Auth::user()->role === 'supplier';
    }
}

if(!function_exists('generateOrderId')) {
    function generateOrderId($prefix='INV')
    {
        return  $prefix . '-' . date('ymdHis') . rand(11, 99);
    }
}