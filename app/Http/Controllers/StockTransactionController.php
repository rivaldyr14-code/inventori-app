<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockTransactionRequest;
use App\Http\Requests\UpdateStockTransactionRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockTransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $search        = $request->input('search');
        $filterType    = $request->input('filter_type');
        $filterProduct = $request->input('filter_product_id');
        $filterDateFrom = $request->input('filter_date_from');
        $filterDateTo   = $request->input('filter_date_to');
        $sortBy        = $request->input('sort_by', 'created_at');
        $sortDir       = $request->input('sort_dir', 'desc');

        $allowedSorts = ['transaction_number', 'type', 'quantity', 'stock_before', 'stock_after', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = StockTransaction::query()->with(['product' => function ($q) {
            $q->withTrashed();
        }, 'createdBy']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($filterType !== null && $filterType !== '') {
            $query->where('type', $filterType);
        }

        if ($filterProduct !== null && $filterProduct !== '') {
            $query->where('product_id', $filterProduct);
        }

        if ($filterDateFrom) {
            $query->whereDate('created_at', '>=', $filterDateFrom);
        }

        if ($filterDateTo) {
            $query->whereDate('created_at', '<=', $filterDateTo);
        }

        $transactions = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']);

        return Inertia::render('StockTransactions/Index', [
            'transactions' => $transactions,
            'products'     => $products,
            'filters'      => [
                'search'             => $search,
                'filter_type'        => $filterType,
                'filter_product_id'  => $filterProduct,
                'filter_date_from'   => $filterDateFrom,
                'filter_date_to'     => $filterDateTo,
                'sort_by'            => $sortBy,
                'sort_dir'           => $sortDir,
            ],
        ]);
    }

    public function create(): Response
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'current_stock']);

        return Inertia::render('StockTransactions/Create', [
            'products' => $products,
        ]);
    }

    public function store(StoreStockTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $transaction = DB::transaction(function () use ($request, $data) {
            $product = Product::findOrFail($data['product_id']);

            $stockBefore = $product->current_stock;
            $stockAfter = $data['type'] === 'in'
                ? $stockBefore + $data['quantity']
                : $stockBefore - $data['quantity'];

            if ($stockAfter < 0) {
                abort(422, 'Stok tidak boleh negatif.');
            }

            $product->update(['current_stock' => $stockAfter]);

            $data['transaction_number'] = $this->generateTransactionNumber();
            $data['stock_before']       = $stockBefore;
            $data['stock_after']        = $stockAfter;
            $data['created_by']         = auth()->id();

            if ($request->hasFile('attachment')) {
                $year  = now()->year;
                $month = now()->format('m');
                $data['attachment_path'] = $request->file('attachment')
                    ->store("attachments/stock-transactions/{$year}/{$month}", 'private');
            }

            unset($data['attachment']);

            return StockTransaction::create($data);
        });

        session()->flash('success', 'Transaksi stok berhasil dicatat.');

        return redirect()->route('stock-transactions.show', $transaction);
    }

    public function show(StockTransaction $stockTransaction): Response
    {
        $stockTransaction->load(['product' => function ($q) {
            $q->withTrashed();
        }, 'createdBy']);

        $audits = $stockTransaction->audits()->with('user')->latest()->get();

        return Inertia::render('StockTransactions/Show', [
            'stockTransaction' => $stockTransaction,
            'audits'           => $audits,
        ]);
    }

    public function edit(StockTransaction $stockTransaction): Response
    {
        $stockTransaction->load(['product' => function ($q) {
            $q->withTrashed();
        }]);

        $audits = $stockTransaction->audits()->with('user')->latest()->get();

        return Inertia::render('StockTransactions/Edit', [
            'stockTransaction' => $stockTransaction,
            'audits'           => $audits,
        ]);
    }

    public function update(UpdateStockTransactionRequest $request, StockTransaction $stockTransaction): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $year  = now()->year;
            $month = now()->format('m');
            $data['attachment_path'] = $request->file('attachment')
                ->store("attachments/stock-transactions/{$year}/{$month}", 'private');
        }

        unset($data['attachment']);

        $stockTransaction->update($data);

        session()->flash('success', 'Transaksi stok berhasil diperbarui.');

        return redirect()->route('stock-transactions.show', $stockTransaction);
    }

    public function destroy(StockTransaction $stockTransaction): RedirectResponse
    {
        DB::transaction(function () use ($stockTransaction) {
            $product = Product::find($stockTransaction->product_id);
            if ($product) {
                $revertStock = $stockTransaction->type === 'in'
                    ? $product->current_stock - $stockTransaction->quantity
                    : $product->current_stock + $stockTransaction->quantity;
                $product->update(['current_stock' => max(0, $revertStock)]);
            }
            $stockTransaction->delete();
        });

        session()->flash('success', 'Transaksi stok berhasil dihapus dan stok dikembalikan.');

        return redirect()->route('stock-transactions.index');
    }

    private function generateTransactionNumber(): string
    {
        $prefix = 'TXN-' . now()->format('Ymd') . '-';
        $last = StockTransaction::where('transaction_number', 'like', "{$prefix}%")
            ->orderBy('transaction_number', 'desc')
            ->value('transaction_number');

        if ($last) {
            $seq = (int) substr($last, -4) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
