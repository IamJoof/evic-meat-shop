<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClaimAccountController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'exists:orders,order_number'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->where('customer_email', $validated['email'])
            ->firstOrFail();

        // If user already exists with this email, authenticate or error
        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            return back()->withErrors(['email' => 'An account with this email already exists. Please log in to view your orders.']);
        }

        // Create new user account from guest order
        $user = User::create([
            'name' => $order->customer_name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'phone' => $order->customer_phone,
            'email_verified_at' => now(),
        ]);

        // Link all previous orders from this guest email to the new user account
        Order::where('customer_email', $validated['email'])
            ->whereNull('user_id')
            ->update([
                'user_id' => $user->id,
            ]);

        Auth::login($user);

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Your account has been created and your orders have been linked!');
    }
}
