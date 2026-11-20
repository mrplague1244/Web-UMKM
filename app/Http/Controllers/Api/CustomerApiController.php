<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyLog;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerApiController extends Controller
{
    /**
     * Public Catalog Products
     */
    public function getProducts()
    {
        $products = Product::where('is_active', true)
            ->select('id', 'name', 'sku', 'category', 'selling_price', 'stock', 'description', 'image')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * Register Customer
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:100|unique:customers,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran akun member berhasil!',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'total_points' => $customer->total_points,
                'tier' => $customer->tier,
                'token' => $customer->remember_token,
            ],
        ], 201);
    }

    /**
     * Login Customer
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor HP dan kata sandi wajib diisi.',
            ], 422);
        }

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp atau kata sandi tidak cocok.',
            ], 401);
        }

        $token = Str::random(40);
        $customer->update(['remember_token' => $token]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil! Selamat datang kembali, ' . $customer->name,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'total_points' => $customer->total_points,
                'tier' => $customer->tier,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Helper to authenticate customer from Authorization Bearer token or customer_id
     */
    private function resolveCustomer(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token && $request->hasHeader('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (Str::startsWith($authHeader, 'Bearer ')) {
                $token = trim(Str::substr($authHeader, 7));
            }
        }
        if (!$token && $request->has('token')) {
            $token = $request->input('token');
        }

        if ($token) {
            $customer = Customer::where('remember_token', $token)->first();
            if ($customer) {
                return $customer;
            }
        }

        if ($request->filled('customer_id')) {
            return Customer::find($request->customer_id);
        }

        return null;
    }

    /**
     * Customer Profile & Saldo Poin
     */
    public function getProfile(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'total_points' => $customer->total_points,
                'tier' => $customer->tier,
                'created_at' => $customer->created_at->format('d M Y'),
            ],
        ]);
    }

    /**
     * Customer Transactions History
     */
    public function getTransactions(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $transactions = Transaction::with('items')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }

    /**
     * Claim Points from QR Code or Manual Code
     * Token rule: Belanja Rp 10.000 = 1 Poin
     */
    public function claimPoints(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login terlebih dahulu untuk mengeklaim poin.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'claim_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token klaim atau kode struk wajib diisi.',
            ], 422);
        }

        // Cari transaksi berdasarkan claim_token
        $token = trim($request->claim_token);
        $transaction = Transaction::with('items')->where('claim_token', $token)->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode struk atau QR Code tidak ditemukan. Periksa kembali kode Anda.',
            ], 404);
        }

        if ($transaction->claim_status === 'claimed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Poin untuk transaksi ini (' . $transaction->invoice_code . ') sudah pernah diklaim sebelumnya.',
                'claimed_at' => $transaction->claimed_at ? $transaction->claimed_at->format('d M Y H:i') : null,
            ], 400);
        }

        // Hitung poin: kelipatan Rp 10.000 = 1 poin (minimal 1 jika ada total belanja)
        $pointsEarned = $transaction->potential_points > 0 
            ? $transaction->potential_points 
            : max(1, (int) floor($transaction->total_amount / 10000));

        // Update Transaksi
        $transaction->update([
            'claim_status' => 'claimed',
            'claimed_at' => now(),
            'customer_id' => $customer->id,
            'potential_points' => $pointsEarned,
        ]);

        // Update Total Poin Customer
        $customer->increment('total_points', $pointsEarned);

        // Update Tier jika poin melampaui batas (Silver -> Gold >= 100, Platinum >= 300)
        $newTier = 'Silver';
        if ($customer->total_points >= 300) {
            $newTier = 'Platinum';
        } elseif ($customer->total_points >= 100) {
            $newTier = 'Gold';
        }
        $customer->update(['tier' => $newTier]);

        // Catat di Loyalty Logs
        LoyaltyLog::create([
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'points' => $pointsEarned,
            'type' => 'earn',
            'description' => 'Klaim poin transaksi ' . $transaction->invoice_code . ' (Rp ' . number_format($transaction->total_amount, 0, ',', '.') . ')',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Selamat! Anda berhasil mengeklaim ' . $pointsEarned . ' Poin!',
            'data' => [
                'points_earned' => $pointsEarned,
                'total_points' => $customer->fresh()->total_points,
                'tier' => $customer->fresh()->tier,
                'transaction' => [
                    'invoice_code' => $transaction->invoice_code,
                    'total_amount' => $transaction->total_amount,
                    'items_count' => $transaction->items->count(),
                    'date' => $transaction->created_at->format('d M Y H:i'),
                ],
            ],
        ]);
    }
}
