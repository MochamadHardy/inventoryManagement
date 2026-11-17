<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth Profile & Logout
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // -------------------------------------------------------------
    // FASE 4: MASTER DATA ENDPOINTS
    // -------------------------------------------------------------
    Route::apiResource('branches', BranchController::class)->middleware('role:super_admin');
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('products', ProductController::class);

    // -------------------------------------------------------------
    // FASE 5: MUTASI STOK & INVENTARIS
    // -------------------------------------------------------------
    Route::prefix('inventory')->group(function () {
        Route::get('/stocks', [InventoryController::class, 'branchStocks']);
        Route::get('/movements', [InventoryController::class, 'stockMovements']);
    });

    Route::prefix('stock-transfers')->group(function () {
        Route::get('/', [StockTransferController::class, 'index']);
        Route::post('/', [StockTransferController::class, 'store']);
        Route::get('/{id}', [StockTransferController::class, 'show']);
        Route::post('/{id}/send', [StockTransferController::class, 'send']);
        Route::post('/{id}/receive', [StockTransferController::class, 'receive']);
    });

    // -------------------------------------------------------------
    // VERIFIKASI RBAC TESTING
    // -------------------------------------------------------------
    Route::prefix('rbac-test')->group(function () {
        Route::get('/owner-only', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => 'Halo Owner / Super Admin! Anda memiliki akses penuh.',
                'user' => $request->user()->name,
            ]);
        })->middleware('role:super_admin');

        Route::get('/warehouse-only', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => 'Halo Petugas Gudang! Anda dapat mengelola mutasi dan surat jalan.',
                'user' => $request->user()->name,
            ]);
        })->middleware('role:central_warehouse');

        Route::get('/cashier-only', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => 'Halo Kasir Cabang! Anda dapat membuka modul kasir POS.',
                'user' => $request->user()->name,
            ]);
        })->middleware('role:branch_cashier');
    });
});
