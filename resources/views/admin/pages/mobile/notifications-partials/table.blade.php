<tbody id="notifications-table-body" data-notifications-table-shell>
    @forelse ($notifications as $notification)
        @php
            $data = $notification->data ?? [];
            $typeLabels = [
                'promo' => ['label' => 'Promo', 'class' => 'badge-info'],
                'informasi' => ['label' => 'Informasi', 'class' => 'badge-primary'],
                'konfirmasi' => ['label' => 'Konfirmasi', 'class' => 'badge-success'],
            ];
            $resolveUrl = function (?string $url) {
                if (! $url) {
                    return null;
                }

                $url = trim($url);

                if ($url === '') {
                    return null;
                }

                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                    return $url;
                }

                return url($url);
            };
            $notificationType = $data['type'] ?? 'informasi';
            $typeMeta = $typeLabels[$notificationType] ?? ['label' => ucfirst($notificationType), 'class' => 'badge-secondary'];
            $title = $data['title'] ?? 'Notifikasi';
            $message = trim(preg_replace('/\s+/u', ' ', (string) ($data['message'] ?? '')));
            $url = $resolveUrl($data['url'] ?? null);
        @endphp
        <tr>
            <td>
                <div class="fw-bold text-dark">{{ $title }}</div>
                @if (!empty($data['meta']['service_request_code'] ?? null))
                    <div class="text-muted small mt-1">{{ $data['meta']['service_request_code'] }}</div>
                @endif
            </td>
            <td>
                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($message, 110) }}</div>
            </td>
            <td>
                <span class="badge badge-sm {{ $typeMeta['class'] }}">{{ $typeMeta['label'] }}</span>
            </td>
            <td>
                <span class="badge badge-sm badge-{{ ($notification->read_at ? 'secondary' : 'success') }}">
                    {{ $notification->read_at ? 'Read' : 'New' }}
                </span>
            </td>
            <td>
                <div class="small text-muted">
                    {{ optional($notification->created_at)?->format('d M Y, H:i') }}
                </div>
                <div class="small text-muted mt-1">
                    {{ optional($notification->created_at)?->diffForHumans() }}
                </div>
            </td>
            <td>
                <div class="d-flex flex-wrap gap-2">
                    @if ($url)
                        <a href="{{ $url }}" class="btn btn-light btn-sm">Buka</a>
                    @endif
                    @if ($notification->read_at)
                        <span class="btn btn-outline-secondary btn-sm disabled">Dibaca</span>
                    @else
                        <span class="btn btn-outline-success btn-sm disabled">Baru</span>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6">
                <div class="py-5 text-center text-muted">
                    <div class="fw-semibold">Belum ada notifikasi.</div>
                    <div class="small mt-1">Coba ubah filter atau kirim notifikasi baru.</div>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
