<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Notifications\NotificationCatalog;
use App\Services\NotificationTemplateService;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    use AdminView;

    public function __construct(
        protected NotificationTemplateService $service
    ) {
        $this->setView('admin.pages.mobile.notification-templates');
    }

    /** Daftar event notifikasi + template-nya (dikelompokkan per group). */
    public function index()
    {
        $events = NotificationCatalog::events();
        $templates = NotificationTemplate::orderByDesc('is_default')->orderBy('id')->get()->groupBy('event_key');

        // Kelompokkan event berdasarkan 'group'.
        $groups = [];
        foreach ($events as $key => $event) {
            $groups[$event['group'] ?? 'Lainnya'][$key] = $event;
        }
        ksort($groups);

        return $this->view('index', compact('groups', 'templates'));
    }

    /** Form buat template baru (custom). */
    public function create()
    {
        $events = NotificationCatalog::events();

        return $this->view('create', compact('events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_key' => ['required', 'string'],
            'channel' => ['required', 'in:email,push,in_app,sms'],
            'audience' => ['required', 'in:user,admin'],
            'name' => ['required', 'string', 'max:150'],
        ]);

        // Awali dari teks default catalog (bila ada) agar admin tinggal menyunting.
        $event = NotificationCatalog::events()[$data['event_key']] ?? null;
        $default = $event['templates'][$data['channel'] . ':' . $data['audience']] ?? ['subject' => '', 'body' => ''];

        $template = NotificationTemplate::create([
            'event_key' => $data['event_key'],
            'channel' => $data['channel'],
            'audience' => $data['audience'],
            'name' => $data['name'],
            'subject' => $default['subject'] ?? '',
            'body' => $default['body'] ?: ' ',
            'is_active' => false,
            'is_default' => false,
        ]);

        return redirect()
            ->route('admin.mobile.notification_templates.edit', $template->id)
            ->with('success', 'Template dibuat. Sunting isinya lalu aktifkan.');
    }

    public function destroy(int $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        if ($template->is_default) {
            return redirect()
                ->route('admin.mobile.notification_templates.edit', $template->id)
                ->with('error', 'Template default bawaan tidak bisa dihapus. Nonaktifkan saja bila perlu.');
        }

        $template->delete();

        return redirect()
            ->route('admin.mobile.notification_templates')
            ->with('success', 'Template custom dihapus.');
    }

    public function edit(int $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $event = NotificationCatalog::events()[$template->event_key] ?? null;

        $variables = array_merge(NotificationCatalog::globalVariables(), $event['variables'] ?? []);
        $eventLabel = $event['label'] ?? $template->event_key;
        $hasDefault = isset($event['templates'][$template->channel . ':' . $template->audience]);

        return $this->view('edit', compact('template', 'variables', 'eventLabel', 'hasDefault'));
    }

    public function update(Request $request, int $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $template->update([
            'name' => $data['name'],
            'subject' => $data['subject'] ?? '',
            'body' => $data['body'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.mobile.notification_templates.edit', $template->id)
            ->with('success', 'Template notifikasi berhasil disimpan.');
    }

    /** Preview live: substitusi variabel dengan nilai contoh. */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'event_key' => ['required', 'string'],
            'channel' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
        ]);

        $ctx = $this->service->sampleContext($data['event_key']);
        $plain = in_array($data['channel'] ?? '', ['push', 'in_app'], true);

        return response()->json([
            'subject' => $this->service->renderText((string) ($data['subject'] ?? ''), $ctx),
            'body' => $this->service->renderText((string) ($data['body'] ?? ''), $ctx, $plain),
            'plain' => $plain,
        ]);
    }

    /** Kembalikan template ke teks default dari catalog. */
    public function reset(int $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $event = NotificationCatalog::events()[$template->event_key] ?? null;
        $default = $event['templates'][$template->channel . ':' . $template->audience] ?? null;

        if ($default) {
            $template->update([
                'subject' => $default['subject'] ?? '',
                'body' => $default['body'] ?? '',
            ]);
        }

        return redirect()
            ->route('admin.mobile.notification_templates.edit', $template->id)
            ->with('success', 'Template dikembalikan ke default.');
    }

    /** Duplikat jadi template custom (non-default, nonaktif) untuk diedit admin. */
    public function duplicate(int $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $copy = $template->replicate(['is_default']);
        $copy->is_default = false;
        $copy->is_active = false;
        $copy->name = $template->name . ' (custom)';
        $copy->save();

        return redirect()
            ->route('admin.mobile.notification_templates.edit', $copy->id)
            ->with('success', 'Template custom dibuat. Aktifkan setelah selesai mengedit.');
    }
}
