# DG SPORTS - Canlı Maç İzleme Platformu

![DG SPORTS](https://img.shields.io/badge/DG%20SPORTS-Live%20Streaming-dc2626)
![DiziPortal.Com](https://img.shields.io/badge/Developer-DiziPortal.Com-dc2626)
![Version](https://img.shields.io/badge/Version-1.0.0-green)

## 🏈 Proje Hakkında

DG SPORTS, modern ve kullanıcı dostu arayüzü ile canlı spor maçlarını ve 7/24 spor kanallarını izleyebileceğiniz profesyonel bir web platformudur. Siyah ve kırmızı tonlarında göz yormayan degrade tasarımı ile geliştirilmiştir.

### ✨ Özellikler

- 🎬 **PlayerJS Entegrasyonu**: HLS, TS, MP4 ve diğer tüm video formatları desteği
- 📱 **Responsive Tasarım**: Tüm cihazlarla uyumlu (mobil, tablet, masaüstü)
- ⚡ **CORS Desteği**: Asla sorun çıkmayan, tüm cihazlarla uyumlu yayın sistemi
- 🔧 **Admin Panel**: Tam içerik yönetim sistemi
- 📊 **Anlık İzleyici Sayısı**: Gerçek zamanlı viewer counter
- 🎨 **Dark Theme**: Göz yormayan siyah-kırmızı gradient tasarım
- 🏢 **Shared Hosting Uyumlu**: cPanel paylaşımlı hostinglerde sorunsuz çalışır
- 📱 **Sosyal Medya Entegrasyonu**: Telegram, Instagram, Twitter, TikTok desteği

### 🎯 Ana Bölümler

1. **Canlı Maçlar**: Takım isimleri, logoları, maç saati ve konum bilgileri
2. **7/24 Kanallar**: Spor kanalları kategorilere göre filtrelenebilir
3. **İletişim**: Sosyal medya bağlantıları
4. **Admin Panel**: Tüm içerik yönetimi

## 🚀 Kurulum

### Gereksinimler

- Web sunucusu (Apache/Nginx)
- Modern web tarayıcısı
- İsteğe bağlı: PHP desteği (gelişmiş özellikler için)

### Adım 1: Dosyaları Yükleme

1. Tüm dosyaları web sunucunuzun root dizinine yükleyin
2. Dosya izinlerini kontrol edin (755 önerilen)

### Adım 2: Konfigürasyon

#### Temel Ayarlar

Herhangi bir ek konfigürasyon gerekmez. Site hazır kullanım için optimize edilmiştir.

#### .htaccess Optimizasyonu (İsteğe Bağlı)

```apache
# DG SPORTS - DiziPortal.Com Optimizations
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Error pages
ErrorDocument 404 /index.html
ErrorDocument 403 /index.html
```

## 🔧 Admin Panel Kullanımı

### Giriş Bilgileri

- **Kullanıcı Adı**: `admin`
- **Şifre**: `dgsports2024`

> ⚠️ **Güvenlik Uyarısı**: Kurulum sonrası `assets/js/diziportal-admin.js` dosyasında şifreyi değiştirin!

### Admin Panel Özellikleri

#### 🏈 Maç Yönetimi

- **Maç Ekleme**: Takım isimleri, logoları, maç saati, konum ve yayın URL'si
- **Maç Düzenleme**: Mevcut maç bilgilerini güncelleme
- **Maç Silme**: İstenmeyen maçları kaldırma
- **Otomatik Durum**: Maç saatine göre otomatik "CANLI" veya "YAKINDA" durumu

#### 📺 Kanal Yönetimi

- **Kanal Ekleme**: Kanal adı, kategorisi, logosu ve yayın URL'si
- **Kategori Sistemi**: Futbol, Basketbol, Genel Spor kategorileri
- **Kanal Düzenleme**: Mevcut kanal bilgilerini güncelleme
- **Kanal Silme**: İstenmeyen kanalları kaldırma

#### ⚙️ Site Ayarları

- **Site Başlığı ve Açıklaması**: Meta bilgileri düzenleme
- **Sosyal Medya Linkleri**: Telegram, Instagram, Twitter, TikTok URL'leri
- **Anlık Kaydetme**: Tüm değişiklikler otomatik kaydedilir

## 🎬 PlayerJS Entegrasyonu

### Desteklenen Formatlar

- **HLS**: `.m3u8` playlist dosyaları
- **TS**: Transport Stream dosyaları
- **MP4**: MPEG-4 video dosyaları
- **MKV**: Matroska video dosyaları
- **AVI**: Audio Video Interleave dosyaları
- **WebM**: Web optimized video dosyaları

### CORS Desteği

Platform otomatik CORS proxy sistemi ile tüm yayın kaynaklarını destekler:

- Otomatik proxy algılama
- Çoklu proxy fallback sistemi
- Cihaz uyumluluğu garantisi

### Örnek Yayın URL'leri

```
# HLS Stream
https://example.com/stream/playlist.m3u8

# MP4 Direct
https://example.com/video.mp4

# Transport Stream
https://example.com/stream.ts
```

## 📱 Responsive Tasarım

### Desteklenen Cihazlar

- **Masaüstü**: 1200px ve üzeri
- **Tablet**: 768px - 1199px
- **Mobil**: 320px - 767px

### Özellikler

- Akıllı grid sistem
- Dokunmatik friendly arayüz
- Mobil optimized player
- Hızlı loading performansı

## 🎨 Özelleştirme

### Renk Değişkenleri

`assets/css/diziportal-styles.css` dosyasında:

```css
:root {
    --primary-black: #0a0a0a;
    --secondary-black: #1a1a1a;
    --accent-red: #dc2626;
    --accent-red-dark: #991b1b;
    --accent-red-light: #ef4444;
}
```

### Logo Değiştirme

`assets/images/` klasörüne kendi logonuzu ekleyin ve HTML'de güncelleyin.

## 🏢 Shared Hosting Optimizasyonları

### Performans İyileştirmeleri

- Otomatik sıkıştırma sistemi
- Azaltılmış yedekleme sıklığı
- Optimized localStorage kullanımı
- Bandwidth tasarruf algoritmaları

### Uyumluluk

- ✅ cPanel hosting
- ✅ Shared hosting ortamları
- ✅ Düşük kaynak kullanımı
- ✅ Hızlı yükleme süreleri

## 📊 Analitik ve İstatistikler

### Otomatik Takip

- Anlık izleyici sayıları
- Maç popülerlik istatistikleri
- Kanal kullanım verileri
- Real-time viewer counter

### Google Analytics Desteği

```javascript
// Google Analytics entegrasyonu için
gtag('event', 'video_play', {
    video_title: title,
    video_type: type,
    video_id: id
});
```

## 🔒 Güvenlik Özellikleri

### İçerik Güvenliği

- XSS koruması
- CSRF token sistemi
- Güvenli veri validation
- Sanitized user inputs

### Session Yönetimi

- Güvenli admin sessions
- Otomatik timeout
- Encrypted localStorage
- Secure cookie handling

## 🆘 Sorun Giderme

### Yaygın Sorunlar

#### Player Çalışmıyor

```javascript
// Console'da kontrol edin:
console.log('PlayerJS available:', typeof Playerjs !== 'undefined');
```

**Çözüm**: PlayerJS CDN bağlantısını kontrol edin.

#### Yayın Açılmıyor

1. CORS ayarlarını kontrol edin
2. Yayın URL'sinin geçerli olduğunu doğrulayın
3. Network sekmesinde hata olup olmadığına bakın

#### Admin Panel Açılmıyor

1. Doğru kullanıcı adı/şifre kullandığınızdan emin olun
2. Browser console'da JavaScript hataları kontrol edin
3. localStorage'ın aktif olduğunu doğrulayın

### Hata Raporlama

Karşılaştığınız sorunları şu bilgilerle birlikte bildirin:

- Tarayıcı versiyonu
- İşletim sistemi
- Hata mesajı (varsa)
- Adım adım tekrarlama yöntemi

## 📝 Changelog

### v1.0.0 (2024)

- ✨ İlk stabil sürüm
- 🎬 PlayerJS entegrasyonu
- 📱 Responsive tasarım
- 🔧 Admin panel
- 📊 Analytics entegrasyonu
- 🏢 Shared hosting optimizasyonları

## 🤝 Katkıda Bulunma

Bu proje sürekli geliştirilmektedir. Katkılarınızı bekliyoruz!

### Geliştirme Ortamı

1. Proje dosyalarını indirin
2. Yerel web sunucusu kurun
3. `index.html` dosyasını açın
4. Değişikliklerinizi yapın

## 📞 İletişim ve Destek

- **Geliştirici**: DiziPortal.Com
- **Website**: [https://diziportal.com](https://diziportal.com)
- **E-posta**: info@diziportal.com

## 📄 Lisans

Bu proje DiziPortal.Com tarafından geliştirilmiştir. Tüm hakları saklıdır.

---

### 🌟 Özellik İstekleri

Yeni özellik önerilerinizi aşağıdaki kategorilerde değerlendiriyoruz:

- **Player İyileştirmeleri**: Yeni format desteği, kalite seçenekleri
- **UI/UX**: Tasarım iyileştirmeleri, animasyonlar
- **Admin Panel**: Yeni yönetim özellikleri
- **Analitik**: Detaylı raporlama sistemleri
- **Güvenlik**: Ek güvenlik katmanları

---

**DG SPORTS** - Profesyonel spor yayın deneyimi için tasarlandı.
*Geliştirici: DiziPortal.Com*
