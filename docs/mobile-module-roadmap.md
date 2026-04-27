# Mobile Module Roadmap

## Goal

Menjadikan dashboard admin web yang sudah ada sebagai backoffice utama untuk aplikasi mobile order jasa kontraktor, tanpa membuat dashboard terpisah.

## Initial Scope

- Manajemen user mobile
- Manajemen layanan mobile
- Banner mobile
- Produk furniture / add-on
- Pengaturan theme dan layout home
- Push notification
- Live chat

## Suggested Data Separation

Beberapa data bisa memakai tabel existing, tetapi sebaiknya entitas berikut dipisah dari company profile jika kebutuhan mobile lebih transactional:

- `mobile_users`
- `mobile_services` atau extensi dari `services`
- `mobile_banners`
- `mobile_home_configs`
- `mobile_products`
- `mobile_notification_campaigns`
- `mobile_device_tokens`
- `chat_conversations`
- `chat_messages`

## Recommended Build Order

1. Finalisasi area admin `mobile` dan struktur menu.
2. Tentukan model data untuk user, layanan, banner, produk, dan layout home.
3. Buat API khusus mobile berdasarkan struktur data yang sudah stabil.
4. Tambahkan autentikasi customer mobile.
5. Implementasikan push notification.
6. Implementasikan live chat.

## Notes

- Banner mobile sebaiknya dipisah dari banner website.
- Layout home mobile lebih aman disimpan dalam JSON config yang bisa diurutkan dari admin.
- Live chat perlu keputusan awal: internal build atau integrasi pihak ketiga.
- Push notification bisa mulai dari Expo Push Service jika app mobile masih berbasis Expo.
