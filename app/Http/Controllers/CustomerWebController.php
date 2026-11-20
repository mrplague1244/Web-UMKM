<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyLog;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerWebController extends Controller
{
    /**
     * Customer Landing Page & Product Catalog
     */
    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = ['Semua', ...Product::where('is_active', true)->pluck('category')->unique()->values()->all()];

        return view('customer.index', compact('products', 'categories'));
    }

    /**
     * Show Customer Login Form
     */
    public function showLogin(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        $redirect = $request->query('redirect');

        return view('customer.login', compact('redirect'));
    }

    /**
     * Process Customer Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return back()->withInput($request->only('phone'))->withErrors([
                'phone' => 'Nomor WhatsApp atau kata sandi tidak cocok.',
            ]);
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirect = $request->input('redirect');
        if ($redirect && str_starts_with($redirect, '/')) {
            return redirect($redirect)->with('success', 'Selamat datang kembali, ' . $customer->name . '!');
        }

        return redirect()->route('customer.dashboard')->with('success', 'Selamat datang kembali, ' . $customer->name . '!');
    }

    /**
     * Show Customer Register Form
     */
    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.register');
    }

    /**
     * Process Customer Registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:100|unique:customers,email',
            'password' => 'required|string|min:6',
        ], [
            'phone.unique' => 'Nomor WhatsApp sudah terdaftar sebagai member.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'total_points' => 0,
            'tier' => 'Silver',
            'remember_token' => Str::random(40),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')->with('success', 'Pendaftaran akun member berhasil! Selamat bergabung, ' . $customer->name . '!');
    }

    /**
     * Process Customer Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari akun member.');
    }

    /**
     * Customer Member Dashboard & Loyalty Card
     */
    public function dashboard()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login')->with('error', 'Silakan masuk untuk mengakses kartu member Anda.');
        }

        $customer = Auth::guard('customer')->user();

        $transactions = Transaction::with('items')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.dashboard', compact('customer', 'transactions'));
    }

    /**
     * Show QR Scanner & Manual Receipt Code Input
     */
    public function showScan(Request $request)
    {
        $token = $request->query('token') ?? $request->query('claim_token');

        return view('customer.scan', compact('token'));
    }

    /**
     * Claim Points from QR Code or Manual Receipt Code
     */
    public function claimPoints(Request $request)
    {
        $rawToken = trim($request->input('claim_token', ''));

        // If scanned text is a full URL, extract the token query param
        if (filter_var($rawToken, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($rawToken);
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (!empty($queryParams['token'])) {
                    $rawToken = $queryParams['token'];
                } elseif (!empty($queryParams['claim_token'])) {
                    $rawToken = $queryParams['claim_token'];
                }
            }
        }

        $isJson = $request->expectsJson() || $request->ajax();

        if (!Auth::guard('customer')->check()) {
            $loginRedirect = route('customer.login', ['redirect' => '/scan?token=' . urlencode($rawToken)]);
            if ($isJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Silakan login terlebih dahulu untuk mengeklaim poin belanja.',
                    'redirect' => $loginRedirect,
                ], 401);
            }
            return redirect($loginRedirect)->with('error', 'Silakan login terlebih dahulu untuk mengeklaim poin belanja.');
        }

        if (empty($rawToken)) {
            if ($isJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token klaim atau kode struk wajib diisi.',
                ], 422);
            }
            return back()->with('error', 'Token klaim atau kode struk wajib diisi.');
        }

        $transaction = Transaction::with('items')->where('claim_token', $rawToken)->first();

        if (!$transaction) {
            $errMsg = 'Kode struk atau QR Code tidak ditemukan. Periksa kembali kode Anda.';
            if ($isJson) {
                return response()->json(['status' => 'error', 'message' => $errMsg], 404);
            }
            return back()->with('error', $errMsg);
        }

        if ($transaction->claim_status === 'claimed') {
            $errMsg = 'Poin untuk transaksi ini (' . $transaction->invoice_code . ') sudah pernah diklaim sebelumnya.';
            if ($isJson) {
                return response()->json(['status' => 'error', 'message' => $errMsg], 400);
            }
            return back()->with('error', $errMsg);
        }

        $pointsEarned = $transaction->potential_points > 0
            ? $transaction->potential_points
            : max(1, (int) floor($transaction->total_amount / 10000));

        $customer = Auth::guard('customer')->user();

        // Update transaction
        $transaction->update([
            'claim_status' => 'claimed',
            'claimed_at' => now(),
            'customer_id' => $customer->id,
            'potential_points' => $pointsEarned,
        ]);

        // Update customer points
        $customer->increment('total_points', $pointsEarned);
        $customer->refresh();

        // Update tier
        $newTier = 'Silver';
        if ($customer->total_points >= 300) {
            $newTier = 'Platinum';
        } elseif ($customer->total_points >= 100) {
            $newTier = 'Gold';
        }
        $customer->update(['tier' => $newTier]);

        // Record loyalty log
        LoyaltyLog::create([
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'points' => $pointsEarned,
            'type' => 'earn',
            'description' => 'Klaim poin transaksi ' . $transaction->invoice_code . ' (Rp ' . number_format($transaction->total_amount, 0, ',', '.') . ')',
        ]);

        $successPayload = [
            'points_earned' => $pointsEarned,
            'total_points' => $customer->total_points,
            'tier' => $customer->tier,
            'transaction' => [
                'invoice_code' => $transaction->invoice_code,
                'total_amount' => $transaction->total_amount,
                'items_count' => $transaction->items->count(),
                'date' => $transaction->created_at->format('d M Y H:i'),
            ],
        ];

        if ($isJson) {
            return response()->json([
                'status' => 'success',
                'message' => 'Selamat! Anda berhasil mengeklaim ' . $pointsEarned . ' Poin!',
                'data' => $successPayload,
            ]);
        }

        return redirect()->route('customer.scan')->with('claimSuccess', $successPayload);
    }
}
