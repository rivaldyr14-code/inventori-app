<?php

namespace App\Jobs;

use App\Models\ExportImportLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Role;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public string $logId,
        public string $entity,
        public array $filters,
        public array $selectedFields,
        public string $userId,
    ) {}

    public function handle(): void
    {
        $log = ExportImportLog::findOrFail($this->logId);
        $log->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $query = $this->buildQuery();
            $filename = "{$this->entity}_" . now()->format('Ymd_His') . '.xlsx';
            $path = "exports/{$filename}";

            $exportClass = $this->getExportClass();
            Excel::store(new $exportClass($query, $this->selectedFields), $path, 'private');

            $log->update([
                'status'     => 'completed',
                'file_path'  => $path,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $log->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }

    private function buildQuery()
    {
        $search = $this->filters['search'] ?? null;
        $sortBy = $this->filters['sort_by'] ?? 'created_at';
        $sortDir = isset($this->filters['sort_dir']) && in_array(strtolower($this->filters['sort_dir']), ['asc', 'desc']) ? strtolower($this->filters['sort_dir']) : 'desc';

        $allowedSorts = match ($this->entity) {
            'categories' => ['name', 'description', 'is_active', 'created_at'],
            'products' => ['sku', 'name', 'price', 'current_stock', 'is_active', 'created_at'],
            'stock-transactions' => ['transaction_number', 'type', 'quantity', 'is_active', 'created_at'],
            'users' => ['name', 'email', 'is_active', 'created_at'],
            'roles' => ['name', 'is_active', 'created_at'],
            default => ['created_at'],
        };
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        switch ($this->entity) {
            case 'categories':
                $query = Category::query();
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
                if (! empty($this->filters['filter_is_active'])) {
                    $query->where('is_active', (bool) $this->filters['filter_is_active']);
                }
                break;

            case 'products':
                $query = Product::query()->with('category');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('sku', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                }
                if (! empty($this->filters['filter_category_id'])) {
                    $query->where('category_id', $this->filters['filter_category_id']);
                }
                if (! empty($this->filters['filter_is_active'])) {
                    $query->where('is_active', (bool) $this->filters['filter_is_active']);
                }
                break;

            case 'stock-transactions':
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
                if (! empty($this->filters['filter_type'])) {
                    $query->where('type', $this->filters['filter_type']);
                }
                break;

            case 'users':
                $query = User::query()->with('roles');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                }
                break;

            case 'roles':
                $query = Role::query();
                if ($search) {
                    $query->where('name', 'like', "%{$search}%");
                }
                break;

            default:
                throw new \InvalidArgumentException("Unknown entity: {$this->entity}");
        }

        return $query->orderBy($sortBy, $sortDir);
    }

    private function getExportClass(): string
    {
        return match ($this->entity) {
            'categories'          => \App\Exports\CategoryExport::class,
            'products'            => \App\Exports\ProductExport::class,
            'stock-transactions'  => \App\Exports\StockTransactionExport::class,
            'users'               => \App\Exports\UserExport::class,
            'roles'               => \App\Exports\RoleExport::class,
            default               => throw new \InvalidArgumentException("Unknown entity: {$this->entity}"),
        };
    }
}
