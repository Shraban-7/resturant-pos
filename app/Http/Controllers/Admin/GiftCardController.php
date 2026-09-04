<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    public function index()
    {
        $giftCards = GiftCard::self()->latest('id')->paginate(15);
        return view('admin.gift-cards.index', compact('giftCards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'initial_value' => 'required|numeric|min:1',
            'recipient_email' => 'nullable|email',
            'recipient_phone' => 'nullable|string',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        $code = 'GC-' . strtoupper(Str::random(10));

        GiftCard::create([
            'seller_id' => panel_owner_id(),
            'code' => $code,
            'initial_value' => $request->initial_value,
            'balance' => $request->initial_value,
            'recipient_email' => $request->recipient_email,
            'recipient_phone' => $request->recipient_phone,
            'expiry_date' => $request->expiry_date,
            'is_active' => true,
        ]);

        return back()->with('success', "Gift Card created successfully: {$code}");
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:64',
        ]);

        $card = GiftCard::self()
            ->where('code', strtoupper($data['code']))
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today());
            })
            ->first();

        if (!$card || $card->balance <= 0) {
            return errorResponse('Invalid, expired, or depleted gift card');
        }

        return apiResponse([
            'code' => $card->code,
            'balance' => (float) $card->balance,
        ], 'Gift card verified');
    }
}



