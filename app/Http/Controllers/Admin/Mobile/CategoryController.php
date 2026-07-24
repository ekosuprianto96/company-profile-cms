<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\CategoryAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected CategoryAdminService $service
    ) {
        $this->setView('admin.pages.mobile.categories');
    }

    public function index()
    {
        return $this->view('index', ['categories' => $this->service->tree()]);
    }

    public function forms(Request $request)
    {
        try {
            $category = $request->filled('id_category')
                ? $this->service->find((int) $request->id_category)
                : null;

            // Opsi induk: saat edit, kecualikan kategori ini & keturunannya.
            $parentOptions = $this->service->parentOptions($category?->id);

            return $this->setView('admin.components.forms.')
                ->view($request->view, compact('category', 'parentOptions'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->service->create($this->validatePayload($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan kategori.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $this->service->update($id, $this->validatePayload($request, $id));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah kategori.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_category);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus kategori.'], $this->statusCode);
        } catch (\Exception $error) {
            // Kode 422 dari service = induk masih punya anak (pesan ramah).
            $code = (int) $error->getCode();

            return response()->json(['message' => $error->getMessage()], $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', $this->notSelfOrDescendant($id)],
            'icon' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /** Cegah kategori jadi anak dari dirinya sendiri / keturunannya. */
    private function notSelfOrDescendant(?int $id): \Closure
    {
        return function ($attribute, $value, $fail) use ($id) {
            if (! $id || ! $value) {
                return;
            }
            $descendants = \App\Models\Category::find($id)?->descendantIds() ?? [];
            if (in_array((int) $value, $descendants, true)) {
                $fail('Induk tidak boleh kategori itu sendiri atau turunannya.');
            }
        };
    }
}
