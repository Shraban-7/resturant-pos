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


if(!function_exists('is_admin')) {
    function is_admin() : bool
    {
        return (bool) Auth::user()?->isAdmin();
    }
}
if(!function_exists('is_employee')) {
    function is_employee() : bool
    {
        return (bool) Auth::user()?->isEmployee();
    }
}
if(!function_exists('can')) {
    function can(string $permission) : bool
    {
        return auth()->check() && auth()->user()->hasPermission($permission);
    }
}
if(!function_exists('panel_owner_id')) {
    function panel_owner_id() : ?int
    {
        return auth()->check() ? auth()->user()->ownerId() : null;
    }
}

if(!function_exists('generateOrderId')) {
    function generateOrderId($prefix='INV')
    {
        return  $prefix . '-' . date('ymdHis') . rand(11, 99);
    }
}

if (! function_exists('active_branch_id')) {
    function active_branch_id(): ?int
    {
        if (! auth()->check()) {
            return null;
        }

        $ownerId = panel_owner_id();

        $hasBranches = \App\Models\Branch::query()
            ->where('admin_id', $ownerId)
            ->where('is_active', true)
            ->exists();

        if (! $hasBranches) {
            return null;
        }

        $sessionId = session('active_branch_id');

        if ($sessionId === 'all') {
            return null;
        }

        if ($sessionId) {
            $exists = \App\Models\Branch::query()
                ->where('admin_id', $ownerId)
                ->whereKey($sessionId)
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return (int) $sessionId;
            }

            session()->forget('active_branch_id');
        }

        $default = \App\Models\Branch::query()
            ->where('admin_id', $ownerId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->value('id');

        if ($default) {
            session(['active_branch_id' => (int) $default]);

            return (int) $default;
        }

        return null;
    }
}

if (! function_exists('active_branch')) {
    function active_branch(): ?\App\Models\Branch
    {
        $id = active_branch_id();

        return $id
            ? \App\Models\Branch::query()->where('admin_id', panel_owner_id())->find($id)
            : null;
    }
}

if (! function_exists('admin_branches')) {
    function admin_branches()
    {
        if (! auth()->check()) {
            return collect();
        }

        return \App\Models\Branch::query()
            ->where('admin_id', panel_owner_id())
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }
}

if (! function_exists('is_all_branches_mode')) {
    function is_all_branches_mode(): bool
    {
        return session('active_branch_id') === 'all';
    }
}

if (! function_exists('human_hour')) {
    // 5 => "5 AM", 13 => "1 PM", 0 => "12 AM", 12 => "12 PM".
    function human_hour(int $hour): string
    {
        $hour = $hour % 24;

        return date('g A', mktime($hour, 0));
    }
}

if (! function_exists('human_time')) {
    // Any datetime => "12 Jan 2026, 7:30 PM".
    function human_time($datetime): string
    {
        if (! $datetime) {
            return '—';
        }

        return \Carbon\Carbon::parse($datetime)->format('d M Y, h:i A');
    }
}

if (! function_exists('human_slot_range')) {
    // ['from' => 5, 'to' => 11] => "5 AM – 11 AM".
    function human_slot_range(int $from, int $to): string
    {
        return human_hour($from).' – '.human_hour($to);
    }
}


