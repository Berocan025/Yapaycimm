# DG SPORTS Admin Dashboard - Yönetici Kılavuzu

![Admin Dashboard](https://img.shields.io/badge/Admin-Dashboard-dc2626)
![Security](https://img.shields.io/badge/Security-First-green)
![DiziPortal.Com](https://img.shields.io/badge/Developer-DiziPortal.Com-dc2626)

## 🔐 Admin Paneline Erişim

### URL ve Giriş Bilgileri

- **Admin URL**: `https://yoursite.com/admin/`
- **Kullanıcı Adı**: `admin`  
- **Şifre**: `dgsports2024`

> ⚠️ **ÖNEMLİ**: İlk kurulumdan sonra mutlaka şifreyi değiştirin!

### Şifre Değiştirme

1. `admin/admin-dashboard.js` dosyasını açın
2. Aşağıdaki bölümü bulun:

```javascript
// Credentials (In production, use secure backend authentication)
credentials: {
    username: 'admin',
    password: 'dgsports2024'  // ← Burayı değiştirin
},
```

3. Yeni şifrenizi girin ve dosyayı kaydedin

## 🎯 Dashboard Özellikleri

### Ana Dashboard

Admin paneli açıldığında göreceğiniz ana ekran:

- **Gerçek Zamanlı İstatistikler**
  - Toplam izleyici sayısı
  - Canlı maç sayısı  
  - Aktif kanal sayısı
  - Sistem uptime oranı

- **Grafik Analizleri**
  - Son 24 saat izleyici grafiği
  - Popüler içerik dağılımı
  - Performans metrikleri

- **Son Aktiviteler**
  - Sistem olayları
  - Kullanıcı etkileşimleri
  - Güvenlik bildirimleri

## 🏈 Canlı Maç Yönetimi

### Yeni Maç Ekleme

1. Sol menüden **"Live Matches"** seçin
2. **"Add New Match"** butonuna tıklayın
3. Maç bilgilerini doldurun:
   - **Home Team**: Ev sahibi takım adı
   - **Away Team**: Deplasman takımı adı
   - **Home Logo URL**: Ev sahibi takım logosu
   - **Away Logo URL**: Deplasman takımı logosu
   - **Match Date & Time**: Maç tarihi ve saati
   - **Location**: Maç yeri
   - **Stream URL**: Yayın URL'si

### Desteklenen Stream Formatları

- **HLS**: `https://example.com/stream.m3u8`
- **MP4**: `https://example.com/stream.mp4`
- **TS**: `https://example.com/stream.ts`
- **WebM**: `https://example.com/stream.webm`

### Maç Durumları

- **🔴 LIVE**: Canlı yayında
- **⏰ Upcoming**: Yakında başlayacak

## 📺 Kanal Yönetimi

### Yeni Kanal Ekleme

1. Sol menüden **"24/7 Channels"** seçin  
2. **"Add New Channel"** butonuna tıklayın
3. Kanal bilgilerini doldurun:
   - **Channel Name**: Kanal adı
   - **Category**: Kategori seçimi
   - **Channel Logo URL**: Kanal logosu
   - **Stream URL**: Yayın URL'si

### Kanal Kategorileri

- **Football**: Futbol kanalları
- **Basketball**: Basketbol kanalları  
- **General Sports**: Genel spor kanalları

### Kanal Kalite Takibi

Admin panelinde her kanal için görebilirsiniz:
- Stream kalitesi yüzdesi
- Uptime oranı
- Anlık izleyici sayısı
- Bağlantı durumu

## 📊 Analytics Sekmesi

### Viewer Analytics

- **24 Saat**: Son 24 saat izleyici verileri
- **7 Gün**: Haftalık trend analizi
- **30 Gün**: Aylık performans raporu

### Traffic Sources

- Doğrudan erişim
- Sosyal medya
- Arama motorları
- Referans siteler

### Device Analytics

- Mobil kullanım oranı
- Masaüstü kullanım oranı
- Tablet kullanım oranı

### Geographic Distribution

- Ülke bazlı izleyici dağılımı
- Şehir bazlı analiz
- Saat dilimi analizi

## ⚙️ Settings (Ayarlar)

### Site Configuration

- **Site Title**: Site başlığı
- **Site Description**: Site açıklaması  
- **Maintenance Mode**: Bakım modu açma/kapama

### Social Media Links

- **Telegram**: `https://t.me/yourchannеl`
- **Instagram**: `https://instagram.com/youraccount`
- **Twitter**: `https://twitter.com/youraccount`
- **TikTok**: `https://tiktok.com/@youraccount`

## 🗄️ Backup & Restore

### Otomatik Yedekleme

Sistem otomatik olarak:
- Her 24 saatte bir tam yedek
- Son 7 günün yedeğini saklar
- Kritik değişikliklerde anlık yedek

### Manuel Yedekleme

1. **Backup & Restore** sekmesine gidin
2. **"Create Backup"** butonuna tıklayın
3. Yedek dosyası otomatik indirilir

### Veri Geri Yükleme

1. **"Import Data"** butonuna tıklayın
2. Yedek JSON dosyasını seçin
3. **"Restore"** butonuna tıklayın

## 🔒 Güvenlik Özellikleri

### IP Bazlı Erişim Kontrolü

`.htaccess` dosyasında IP kısıtlaması yapabilirsiniz:

```apache
<Files ~ "^admin/">
    Order allow,deny
    Deny from all
    # İzin verilen IP'ler
    Allow from 192.168.1.100
    Allow from YOUR_IP_ADDRESS
</Files>
```

### Session Güvenliği

- **8 Saat**: Normal session süresi
- **30 Gün**: "Remember Me" seçeneği ile
- **Otomatik Logout**: Güvenlik için zorunlu çıkış

### Brute Force Koruması

- Yanlış giriş denemelerini loglar
- IP bazlı geçici engellemeler
- Güvenlik uyarı bildirimleri

## ⌨️ Klavye Kısayolları

- **Ctrl+1**: Dashboard
- **Ctrl+2**: Maçlar  
- **Ctrl+3**: Kanallar
- **Ctrl+R**: Verileri yenile
- **Esc**: Modal'ları kapat

## 📱 Responsive Admin

Admin paneli tüm cihazlarda çalışır:

- **Masaüstü**: Tam özellik seti
- **Tablet**: Optimize edilmiş arayüz  
- **Mobil**: Dokunmatik optimizasyonu

## 🆘 Sorun Giderme

### Admin Paneline Giremiyorum

1. **URL Kontrolü**: `/admin/` sonunda `/` var mı?
2. **Şifre Kontrolü**: Büyük/küçük harf duyarlı
3. **Browser Cache**: Önbelleği temizleyin
4. **JavaScript**: JavaScript aktif mi?

### Veriler Yüklenmiyor

1. **Console Kontrol**: F12 > Console hataları
2. **Network Kontrol**: F12 > Network sekmesi
3. **LocalStorage**: Tarayıcı desteği var mı?

### Responsive Sorunları

1. **Viewport**: Meta tag kontrolü
2. **CSS**: Stil dosyası yüklendi mi?
3. **Screen Size**: Min. 320px destekleniyor

## 🔧 Teknik Detaylar

### Dosya Yapısı

```
admin/
├── index.html              # Admin dashboard ana sayfa
├── admin-dashboard.css     # Admin paneli stilleri  
├── admin-dashboard.js      # Admin paneli JavaScript
└── admin-auth.js          # Kimlik doğrulama (opsiyonel)
```

### Veri Formatları

#### Maç Verisi
```json
{
  "id": "match_001",
  "homeTeam": "Galatasaray",
  "awayTeam": "Fenerbahçe", 
  "homeLogo": "https://example.com/gs.png",
  "awayLogo": "https://example.com/fb.png",
  "matchTime": "2024-01-15T20:00:00Z",
  "location": "Türk Telekom Stadyumu",
  "streamUrl": "https://example.com/stream.m3u8",
  "isLive": true,
  "viewers": 15420
}
```

#### Kanal Verisi
```json
{
  "id": "channel_001",
  "name": "beIN SPORTS 1",
  "category": "futbol",
  "logo": "https://example.com/bein1.png", 
  "streamUrl": "https://example.com/bein1.m3u8",
  "isLive": true,
  "viewers": 8750
}
```

## 🔄 Güncellemeler

### Version Control

- **v1.0.0**: İlk admin dashboard
- **v1.0.1**: Güvenlik iyileştirmeleri
- **v1.0.2**: Analytics eklentileri

### Güncelleme Kontrolü

Admin paneli otomatik olarak:
- Yeni versiyonları kontrol eder
- Güvenlik güncellemelerini bildirir
- Backup öncesi uyarı verir

## 📞 Destek ve Yardım

### Teknik Destek

- **Geliştirici**: DiziPortal.Com
- **E-posta**: info@diziportal.com
- **Website**: https://diziportal.com

### Sık Sorulan Sorular

**S: Admin paneli yavaş açılıyor?**
A: Tarayıcı önbelleğini temizleyin ve sayfayı yenileyin.

**S: Grafikler görünmüyor?**  
A: Chart.js CDN bağlantısını kontrol edin.

**S: Veriler kayboluyor?**
A: LocalStorage sınırına ulaşmış olabilir, eski verileri temizleyin.

---

**DG SPORTS Admin Dashboard** - Profesyonel İçerik Yönetimi  
*Geliştirici: DiziPortal.Com*