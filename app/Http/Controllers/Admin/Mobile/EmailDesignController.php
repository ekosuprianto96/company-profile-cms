<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\EmailDesign;
use App\Notifications\NotificationCatalog;
use App\Services\NotificationTemplateService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailDesignController extends Controller
{
    use AdminView;

    public function __construct(
        protected NotificationTemplateService $service
    ) {
        $this->setView('admin.pages.mobile.email-designs');
    }

    /** Galeri desain email (dikelompokkan per kategori). */
    public function index()
    {
        $designs = EmailDesign::withCount('notificationTemplates')
            ->orderByDesc('is_default')->orderBy('category')->orderBy('name')->get();
        $grouped = $designs->groupBy(fn ($d) => $d->category ?: 'Lainnya');

        return $this->view('index', compact('grouped'));
    }

    /** Buat desain baru kosong lalu buka builder. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:60'],
        ]);

        $design = EmailDesign::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'category' => $data['category'] ?? 'Custom',
            'subject' => '',
            'html' => $this->blankHtml(),
            'is_active' => true,
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.mobile.email_designs.builder', $design->id)
            ->with('success', 'Desain email dibuat. Susun tampilannya lalu simpan.');
    }

    /** Halaman builder (GrapesJS) untuk satu desain. */
    public function builder(int $id)
    {
        $design = EmailDesign::findOrFail($id);
        $variables = NotificationCatalog::globalVariables();
        $colorSchemes = \App\Models\EmailColorScheme::orderByDesc('is_default')->orderBy('name')
            ->get(['id', 'name', 'colors', 'is_default']);
        $customBlocks = \App\Models\EmailCustomBlock::latest()->get(['id', 'name', 'html']);

        // Event yang punya template email → untuk pratinjau realistis & kirim uji.
        $events = [];
        foreach (NotificationCatalog::events() as $key => $event) {
            foreach (array_keys($event['templates'] ?? []) as $slot) {
                if (str_starts_with($slot, 'email:')) {
                    $events[$key] = $event['label'] ?? $key;
                    break;
                }
            }
        }

        return $this->view('builder', compact('design', 'variables', 'colorSchemes', 'customBlocks', 'events'));
    }

    /** Simpan skema warna custom (AJAX). */
    public function storeScheme(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'colors' => ['required', 'array', 'min:1', 'max:8'],
            'colors.*' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $scheme = \App\Models\EmailColorScheme::create([
            'name' => $data['name'],
            'colors' => array_values($data['colors']),
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['ok' => true, 'scheme' => $scheme->only(['id', 'name', 'colors', 'is_default'])]);
    }

    public function destroyScheme(Request $request)
    {
        $scheme = \App\Models\EmailColorScheme::findOrFail((int) $request->input('id'));
        if ($scheme->is_default) {
            return response()->json(['ok' => false, 'message' => 'Skema bawaan tidak bisa dihapus.'], 422);
        }
        $scheme->delete();

        return response()->json(['ok' => true]);
    }

    /** Simpan hasil builder (AJAX): projectData + HTML terkompilasi. */
    public function save(Request $request, int $id)
    {
        $design = EmailDesign::findOrFail($id);

        $data = $request->validate([
            'html' => ['required', 'string'],
            'design_json' => ['nullable', 'string'],
            'name' => ['nullable', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'preheader' => ['nullable', 'string', 'max:200'],
        ]);

        $design->update([
            'html' => $data['html'],
            'design_json' => $data['design_json'] ?? $design->design_json,
            'name' => $data['name'] ?? $design->name,
            'subject' => $data['subject'] ?? $design->subject,
            'preheader' => $data['preheader'] ?? $design->preheader,
            'updated_by' => auth()->id(),
        ]);

        // Peringatkan bila slot {{ body }} hilang → isi notifikasi tak akan muncul.
        return response()->json([
            'ok' => true,
            'message' => 'Desain tersimpan.',
            'has_body' => $design->hasBodySlot(),
        ]);
    }

    /** Ubah metadata (nama/kategori/aktif) dari galeri. */
    public function update(Request $request, int $id)
    {
        $design = EmailDesign::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:60'],
            'subject' => ['nullable', 'string', 'max:200'],
        ]);

        $design->update([
            'name' => $data['name'],
            'category' => $data['category'] ?? $design->category,
            'subject' => $data['subject'] ?? $design->subject,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.mobile.email_designs')->with('success', 'Desain diperbarui.');
    }

    public function duplicate(int $id)
    {
        $design = EmailDesign::findOrFail($id);

        $copy = $design->replicate(['is_default']);
        $copy->is_default = false;
        $copy->name = $design->name . ' (salinan)';
        $copy->slug = $this->uniqueSlug($design->name . ' salinan');
        $copy->created_by = auth()->id();
        $copy->save();

        return redirect()
            ->route('admin.mobile.email_designs.builder', $copy->id)
            ->with('success', 'Desain diduplikat. Silakan sunting salinannya.');
    }

    public function destroy(Request $request)
    {
        $design = EmailDesign::findOrFail((int) $request->input('id'));

        $design->delete();

        return redirect()->route('admin.mobile.email_designs')->with('success', 'Desain email dihapus.');
    }

    /** Pratinjau desain (dibuka di tab/iframe). ?event= → pakai isi notifikasi asli. */
    public function preview(int $id, Request $request)
    {
        $design = EmailDesign::findOrFail($id);
        $html = $this->renderDesign($design, $request->query('event'));

        return response($html)->header('Content-Type', 'text/html');
    }

    /** Kirim email uji ke alamat tertentu (sinkron, langsung). */
    public function testSend(Request $request, int $id)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'event' => ['nullable', 'string'],
        ]);

        $design = EmailDesign::findOrFail($id);
        [$ctx] = $this->previewContext($data['event'] ?? null);
        $html = $this->renderDesign($design, $data['event'] ?? null);
        $subject = $this->service->renderText('[UJI] ' . ($design->subject ?: $design->name), $ctx);

        try {
            \Illuminate\Support\Facades\Mail::html($html, function ($m) use ($data, $subject) {
                $m->to($data['email'])->subject($subject);
            });

            return response()->json(['ok' => true, 'message' => 'Email uji terkirim ke ' . $data['email'] . '.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Gagal mengirim: ' . $e->getMessage()], 500);
        }
    }

    /** Simpan komponen terpilih jadi blok custom ("Blok Saya"). */
    public function storeBlock(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'html' => ['required', 'string'],
        ]);

        $block = \App\Models\EmailCustomBlock::create([
            'name' => $data['name'],
            'html' => $data['html'],
            'created_by' => auth()->id(),
        ]);

        return response()->json(['ok' => true, 'block' => $block->only(['id', 'name', 'html'])]);
    }

    public function destroyBlock(Request $request)
    {
        \App\Models\EmailCustomBlock::findOrFail((int) $request->input('id'))->delete();

        return response()->json(['ok' => true]);
    }

    /** Render HTML akhir desain: konteks contoh (+ event asli bila ada) + preheader. */
    private function renderDesign(EmailDesign $design, ?string $event): string
    {
        [$ctx, $body] = $this->previewContext($event);
        $ctx['body'] = $body;

        $html = $this->service->applyDesign((string) $design->html, $ctx);

        return $this->service->injectPreheader($html, (string) $design->preheader);
    }

    /**
     * Konteks + body untuk pratinjau/uji. Bila $event diberikan → pakai sample event
     * + isi email asli event tsb; jika tidak → contoh generik.
     * @return array{0: array<string,mixed>, 1: string}
     */
    private function previewContext(?string $event): array
    {
        $ctx = array_merge([
            'recipient_name' => 'Budi Santoso',
            'support_phone' => '0812-3456-7890',
            'support_whatsapp' => '0812-3456-7890',
        ], $this->service->globalContext());

        if ($event) {
            $ctx = array_merge($this->service->sampleContext($event), $ctx);
            $tpl = \App\Models\NotificationTemplate::where('event_key', $event)
                ->where('channel', 'email')->orderByDesc('is_active')->first();
            $body = $this->service->markdownToHtml($this->service->renderText((string) ($tpl->body ?? ''), $ctx));

            return [$ctx, $body !== '' ? $body : $this->sampleBody()];
        }

        return [$ctx, $this->sampleBody()];
    }

    /** Upload gambar untuk asset manager GrapesJS. */
    public function upload(Request $request)
    {
        $request->validate(['files.*' => ['image', 'max:4096']]);

        $urls = [];
        foreach ((array) $request->file('files', []) as $file) {
            $name = date('Ymd') . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/email-designs'), $name);
            $urls[] = ['src' => asset('assets/images/email-designs/' . $name)];
        }

        return response()->json(['data' => $urls]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'desain';
        $slug = $base;
        $i = 2;
        while (EmailDesign::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function sampleBody(): string
    {
        return '<p>Halo, ini adalah <b>contoh isi pesan</b> yang akan disisipkan otomatis dari template notifikasi. '
            . 'Bagian ini berubah sesuai jenis notifikasi (mis. kode OTP, status pesanan, atau pengumuman).</p>';
    }

    /** Kerangka email lengkap (dari EmailBlocks) sebagai titik awal desain blank. */
    private function blankHtml(): string
    {
        return \App\Support\EmailBlocks::scaffold([
            'heading' => 'Judul Email',
            'greeting' => true,
        ]);
    }
}
