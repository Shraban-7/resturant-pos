<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    public function index()
    {
        $giftCards = GiftCard::self()->latest('id')->paginate(15);
        return view('seller.gift-cards.index', compact('giftCards'));
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
            'seller_id' => auth()->id(),
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
        $card = GiftCard::self()
            ->where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (!$card || $card->balance <= 0) {
            return errorResponse('Invalid or depleted gift card');
        }

        return successResponse('Gift card verified', [
            'code' => $card->code,
            'balance' => (float) $card->balance,
        ]);
    }
}
