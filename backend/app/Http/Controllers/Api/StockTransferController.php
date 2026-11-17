<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ReceiveTransferRequest;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    /**
     * Display a listing of stock transfers with role-based visibility.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = StockTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'receiver',
            'items.variant.product',
        ])->latest('id');

        // Jika bukan super_admin, hanya boleh melihat mutasi yang melibatkan cabangnya
        if ($user->role !== 'super_admin') {
            $branchId = $user->branch_id;
            $query->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            });
        } elseif ($request->filled('branch_id')) {
            $bId = $request->branch_id;
            $query->where(function ($q) use ($bId) {
                $q->where('from_branch_id', $bId)
                  ->orWhere('to_branch_id', $bId);
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $transfers,
        ]);
    }

    /**
     * Store a newly created stock transfer (Surat Jalan).
     */
    public function store(StockTransferRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Generate Nomor Surat Jalan: SJ-YYYYMM-XXXX
        $datePrefix = date('Ym');
        $countToday = StockTransfer::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count() + 1;
        $transferNumber = 'SJ-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        $transfer = DB::transaction(function () use ($validated, $user, $transferNumber, $request) {
            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_branch_id' => $validated['from_branch_id'],
                'to_branch_id' => $validated['to_branch_id'],
                'status' => 'pending',
                'transfer_date' => $validated['transfer_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity_sent' => $item['quantity'],
                    'quantity_received' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Jika opsi auto_send aktif (misal Gudang langsung mengirim fisik barang saat membuat dokumen)
            if (! empty($validated['auto_send'])) {
                $this->executeSend($transfer, $user);
            }

            return $transfer;
        });

        return response()->json([
            'status' => 'success',
            'message' => "Surat Jalan {$transferNumber} berhasil dibuat dengan status {$transfer->status}.",
            'data' => $transfer->load(['fromBranch', 'toBranch', 'creator', 'items.variant.product']),
        ], 201);
    }

    /**
     * Display the specified stock transfer.
     */
    public function show(int $id): JsonResponse
    {
        $transfer = StockTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'receiver',
            'items.variant.product',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $transfer,
        ]);
    }

    /**
     * Send goods: change status from 'pending' to 'in_transit'
     * Locks sender stock, moves to reserved_quantity, and logs stock_movements.
     */
    public function send(Request $request, int $id): JsonResponse
    {
        $transfer = StockTransfer::with('items.variant')->findOrFail($id);
        $user = $request->user();

        // Validasi status
        if ($transfer->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => "Transfer ini sudah tidak dalam status pending (Status saat ini: {$transfer->status}).",
            ], 422);
        }

        // Otorisasi pengirim: Super admin atau petugas dari cabang pengirim
        if ($user->role !== 'super_admin' && (int) $user->branch_id !== (int) $transfer->from_branch_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk mengirim barang dari cabang ini.',
            ], 403);
        }

        DB::transaction(function () use ($transfer, $user) {
            $this->executeSend($transfer, $user);
        });

        return response()->json([
            'status' => 'success',
            'message' => "Barang untuk Surat Jalan {$transfer->transfer_number} resmi dikirim (Status: In Transit).",
            'data' => $transfer->fresh(['fromBranch', 'toBranch', 'items.variant']),
        ]);
    }

    /**
     * Internal helper to execute send logistics and stock reservation.
     */
    protected function executeSend(StockTransfer $transfer, $user): void
    {
        foreach ($transfer->items as $item) {
            $variantId = $item->product_variant_id;
            $qtySent = (float) $item->quantity_sent;

            // Lock stok fisik pengirim dengan lockForUpdate untuk mencegah race condition
            $stockSender = BranchStock::where('branch_id', $transfer->from_branch_id)
                ->where('product_variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (! $stockSender || $stockSender->quantity < $qtySent) {
                $variantName = $item->variant ? $item->variant->variant_name : "ID: {$variantId}";
                $available = $stockSender ? $stockSender->quantity : 0;
                throw new \Exception("Stok tidak mencukupi untuk varian '{$variantName}'. Tersedia: {$available}, Diminta: {$qtySent}.");
            }

            $stockBefore = (float) $stockSender->quantity;
            $stockAfter = $stockBefore - $qtySent;

            // Pindahkan kuantitas fisik ke reserved_quantity
            $stockSender->quantity = $stockAfter;
            $stockSender->reserved_quantity = (float) $stockSender->reserved_quantity + $qtySent;
            $stockSender->save();

            // Catat Kartu Stok (Audit Trail Pengeluaran Mutasi)
            StockMovement::create([
                'branch_id' => $transfer->from_branch_id,
                'product_variant_id' => $variantId,
                'type' => 'transfer_out',
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'quantity' => -$qtySent,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => "Mutasi Keluar Surat Jalan #{$transfer->transfer_number} ke {$transfer->toBranch->name}",
                'user_id' => $user->id,
            ]);
        }

        $transfer->status = 'in_transit';
        $transfer->save();
    }

    /**
     * Receive goods: change status from 'in_transit' to 'received'
     * Removes reserved_quantity in sender, adds actual stock in receiver, and logs stock_movements.
     */
    public function receive(ReceiveTransferRequest $request, int $id): JsonResponse
    {
        $transfer = StockTransfer::with(['fromBranch', 'toBranch', 'items.variant'])->findOrFail($id);
        $user = $request->user();

        if ($transfer->status !== 'in_transit') {
            return response()->json([
                'status' => 'error',
                'message' => "Hanya pengiriman berstatus 'In Transit' yang dapat dikonfirmasi terima (Status saat ini: {$transfer->status}).",
            ], 422);
        }

        // Otorisasi penerima: Super admin atau petugas dari cabang tujuan
        if ($user->role !== 'super_admin' && (int) $user->branch_id !== (int) $transfer->to_branch_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk mengonfirmasi penerimaan barang untuk cabang tujuan ini.',
            ], 403);
        }

        $receivedItemsInput = collect($request->items)->keyBy('id');

        DB::transaction(function () use ($transfer, $user, $receivedItemsInput) {
            foreach ($transfer->items as $item) {
                $variantId = $item->product_variant_id;
                $qtySent = (float) $item->quantity_sent;

                // Tentukan jumlah fisik diterima (default = qtySent jika tidak dispesifikasikan)
                $qtyReceived = $receivedItemsInput->has($item->id)
                    ? (float) $receivedItemsInput[$item->id]['quantity_received']
                    : $qtySent;

                // 1. Kurangi reserved_quantity di cabang pengirim
                $stockSender = BranchStock::where('branch_id', $transfer->from_branch_id)
                    ->where('product_variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                if ($stockSender) {
                    $stockSender->reserved_quantity = max(0, (float) $stockSender->reserved_quantity - $qtySent);
                    $stockSender->save();
                }

                // 2. Tambah kuantitas fisik aktual di cabang penerima
                $stockReceiver = BranchStock::firstOrCreate(
                    [
                        'branch_id' => $transfer->to_branch_id,
                        'product_variant_id' => $variantId,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'min_stock_alert' => 10,
                    ]
                );

                // Lock receiver stock
                $stockReceiver = BranchStock::where('id', $stockReceiver->id)->lockForUpdate()->first();

                $recvBefore = (float) $stockReceiver->quantity;
                $recvAfter = $recvBefore + $qtyReceived;

                $stockReceiver->quantity = $recvAfter;
                $stockReceiver->save();

                // 3. Update item transfer dengan jumlah yang diterima
                $item->quantity_received = $qtyReceived;
                $item->save();

                // 4. Catat Kartu Stok (Audit Trail Penerimaan Mutasi di Cabang Tujuan)
                StockMovement::create([
                    'branch_id' => $transfer->to_branch_id,
                    'product_variant_id' => $variantId,
                    'type' => 'transfer_in',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'quantity' => $qtyReceived,
                    'stock_before' => $recvBefore,
                    'stock_after' => $recvAfter,
                    'notes' => "Mutasi Masuk Surat Jalan #{$transfer->transfer_number} dari {$transfer->fromBranch->name}",
                    'user_id' => $user->id,
                ]);
            }

            $transfer->status = 'received';
            $transfer->received_by = $user->id;
            $transfer->received_date = now();
            $transfer->save();
        });

        return response()->json([
            'status' => 'success',
            'message' => "Barang Surat Jalan {$transfer->transfer_number} berhasil diterima dan stok cabang bertambah.",
            'data' => $transfer->fresh(['fromBranch', 'toBranch', 'receiver', 'items.variant']),
        ]);
    }
}
