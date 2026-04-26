# 🕐 Fitur Estimasi Waktu Penyajian

## Apa itu Fitur Ini?

Fitur estimasi waktu memungkinkan **tenant (penjual)** untuk memberitahu **customer (pembeli)** berapa lama waktu tunggu untuk pesanan mereka. Timer akan menghitung mundur secara real-time dengan format **HH:MM:SS** (jam:menit:detik).

---

## 📲 Untuk Tenant (Penjual) 

### Cara Menggunakan

#### 1. **Lihat Pesanan Baru**
- Buka app MealStation di tab "Beranda"
- Lihat daftar pesanan masuk yang baru

#### 2. **Set Estimasi Waktu**
- Klik tombol pesanan atau "Approve & Proses"
- Sistem akan bertanya: **"Berapa menit estimasi penyajian?"**
- Contoh: Ketik `15` untuk 15 menit
- Klik OK

#### 3. **Pantau Progress**
- Pesanan otomatis masuk ke tab **"Riwayat & Rekap"**
- Cari section **"Sedang Diproses"**
- Lihat countdown timer berjalan
- Format: `⏱️ 00:15:30` (mulai dari 00:15:30 hingga 00:00:00)

#### 4. **Pesanan Siap Disajikan**
- Ketika timer habis, tampil: **"Siap Disajikan!"** (hijau)
- Klik pesanan untuk tandai "Selesai"

---

## 👥 Untuk Customer (Pembeli)

### Cara Melihat Estimasi Waktu

#### 1. **Lihat Riwayat Transaksi**
- Buka app → Tab "Riwayat Transaksi"
- Lihat daftar pesanan Anda

#### 2. **Pantau Waktu Penyajian**
- Pesanan dengan status **"Diproses"** menampilkan countdown
- Format: `⏱️ 00:15:30` (hijau)
- Countdown update otomatis setiap detik

#### 3. **Pesanan Siap**
- Ketika timer habis: tampil **"Siap Disajikan!"** (hijau)
- Pergi ke lokasi untuk mengambil pesanan

---

## ⚙️ Cara Kerja Teknis

### Flow Pesanan
```
1. Customer order → Status: "Belum Bayar"
2. Customer bayar → Status: "Menunggu Diproses"
3. Tenant approve + set 15 menit → Status: "Diproses"
   - Timer mulai: 00:15:00 → 00:14:59 → ... → 00:00:01 → 00:00:00
4. Ketika timer habis → "Siap Disajikan!"
5. Tenant tandai selesai → Status: "Selesai"
```

### Sinkronisasi Real-Time
- **Local Update**: Timer update setiap **1 detik** di UI (smooth)
- **Server Sync**: Sinkronisasi dengan server setiap **10 detik**
- **Akurat**: Jika ada perbedaan waktu, akan otomatis koreksi

---

## 📊 Status Order & Estimasi

| Status | Tampil Estimasi? | Warna | Keterangan |
|--------|------------------|-------|-----------|
| Belum Bayar | ❌ | - | Belum ada estimasi |
| Menunggu Diproses | ❌ | - | Menunggu tenant approve |
| **Diproses** | ✅ | Ungu (#5856d6) | **⏱️ HH:MM:SS** countdown aktif |
| Siap Disajikan | ✅ | Hijau (#34c759) | **⏱️ Siap Disajikan!** |
| Selesai | ❌ | - | Sudah diambil |
| Dibatalkan | ❌ | - | Order batal |

---

## 🔔 Tips & Trik

### Untuk Tenant
- ✅ Estimasi waktu realistis agar customer puas
- ✅ Bisa update estimasi jika ada perubahan
- ⏱️ Jangan terlalu singkat agar pesanan sempurna

### Untuk Customer
- ✅ Pantau timer, jangan pergi terlalu awal
- ✅ Jika timer habis, langsung ambil pesanan
- 💬 Chat atau hubungi tenant jika ada pertanyaan

---

## 🆘 Troubleshooting

### Timer tidak muncul?
**Solusi**:
1. Refresh halaman
2. Pastikan pesanan status "Diproses"
3. Pastikan internet connection bagus

### Timer melompat-lompat?
**Solusi**:
- Normal, terjadi ketika sinkronisasi dengan server
- Akan kembali smooth setelah beberapa detik

### Estimasi waktu terlalu lama/singkat?
**Solusi**:
- Hubungi tenant untuk update
- Tenant bisa approve ulang dengan waktu baru

---

## 📞 Bantuan Lebih Lanjut

Jika ada masalah dengan fitur estimasi waktu:
- Hubungi support: [support email/phone]
- Report bug di app settings

---

**Versi**: 1.0  
**Update**: 26 April 2026
