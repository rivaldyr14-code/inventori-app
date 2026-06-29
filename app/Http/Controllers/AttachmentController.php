<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Valid entity slugs and their corresponding models.
     *
     * @var array<string, class-string>
     */
    private const ENTITY_MODELS = [
        'categories'         => Category::class,
        'products'           => Product::class,
        'stock-transactions' => StockTransaction::class,
    ];

    /**
     * Download the file attachment for a given entity record.
     *
     * @param  string  $entity  One of: categories, products, stock-transactions
     * @param  string  $id      UUID of the entity record
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \Illuminate\Auth\AuthenticationException  403 if unauthenticated
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException  404 if not found
     */
    public function download(string $entity, string $id): StreamedResponse
    {
        // 403 — must be authenticated (enforced by middleware, but double-check here for clarity)
        if (! auth()->check()) {
            abort(403, 'Unauthenticated.');
        }

        // 404 — entity type must be valid
        if (! array_key_exists($entity, self::ENTITY_MODELS)) {
            abort(404, 'Entity type not found.');
        }

        $modelClass = self::ENTITY_MODELS[$entity];

        // Find record; use withTrashed so soft-deleted records still resolve
        /** @var \Illuminate\Database\Eloquent\Model|null $record */
        $record = $modelClass::withTrashed()->find($id);

        // 404 — record must exist
        if (! $record) {
            abort(404, 'Record not found.');
        }

        $path = $record->attachment_path;

        // 404 — record must have an attachment
        if (empty($path)) {
            abort(404, 'No attachment found for this record.');
        }

        // 404 — file must physically exist in private storage
        if (! Storage::disk('private')->exists($path)) {
            abort(404, 'Attachment file not found in storage.');
        }

        // Derive an original filename from the stored path for the download header
        $filename = basename($path);

        return Storage::disk('private')->download($path, $filename);
    }
}
