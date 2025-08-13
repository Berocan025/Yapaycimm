# DG SPORTS - Canlı Maç İzleme Platformu

![DG SPORTS](https://img.shields.io/badge/DG%20SPORTS-Live%20Streaming-dc2626)
![DiziPortal.Com](https://img.shields.io/badge/Developer-DiziPortal.Com-dc2626)
![Version](https://img.shields.io/badge/Version-1.0.0-green)

## 🏈 Proje Hakkında

DG SPORTS, modern ve kullanıcı dostu arayüzü ile canlı spor maçlarını ve 7/24 spor kanallarını izleyebileceğiniz profesyonel bir web platformudur. Siyah ve kırmızı tonlarında göz yormayan degrade tasarımı ile geliştirilmiştir.

## ✨ Özellikler

### 🎯 PlayerJS Entegrasyonu
- **Profesyonel Video Player**: `playerjs.com` entegrasyonu
- **Format Desteği**: HLS, TS, MP4, MKV, AVI, WebM
- **CORS Çözümü**: Otomatik proxy sistemi
- **Cihaz Uyumluluğu**: Mobil, tablet, masaüstü tam uyumluluk

### 📱 Responsive Tasarım
- **Mobile-First**: Öncelikle mobil cihazlar için optimize
- **Adaptive Layout**: Tüm ekran boyutlarına uyum
- **Touch Optimized**: Dokunmatik cihazlar için optimize

### 🚫 CORS Koruması
- **Otomatik Proxy**: Gerektiğinde otomatik proxy kullanımı
- **Fallback Sistemi**: Çoklu proxy desteği
- **Error Handling**: Akıllı hata yönetimi

### 👑 Gelişmiş Admin Panel
- **Ayrı Admin Dashboard**: Ana siteden tamamen bağımsız
- **Real-time Analytics**: Chart.js ile profesyonel grafikler
- **Comprehensive Management**: Tam CRUD işlemleri
- **Security First**: IP bazlı erişim kontrolü
- **Modern UI**: Responsive ve mobile-friendly

### 🎬 Inline Player Sistemi
- **Ana Sayfa Entegrasyonu**: Player direkt ana sayfada açılır
- **Sorunsuz Geçiş**: Modal'sız, akıcı izleme deneyimi
- **Tam Ekran Desteği**: İsteğe bağlı fullscreen modu
- **Responsive Design**: Mobil ve masaüstünde mükemmel
- **Logo Optimizasyonu**: Otomatik resim yükleme ve fallback

### 🛠️ Kolay Kurulum Sistemi
- **3 Adımlı Setup**: Wizard ile hızlı kurulum
- **Otomatik Konfigürasyon**: Elle ayar gerektirmez
- **Örnek İçerik**: Varsayılan maç ve kanallar
- **Admin Hesap Setup**: Kurulum sırasında admin oluşturma

### 📊 İzleyici Sayacı
- **Gerçek Zamanlı**: Anlık izleyici sayısı
- **Otomatik Güncelleme**: Düzenli veri yenileme
- **Visual Feedback**: Görsel izleyici gösterimi

### 🎨 Karanlık Tema
- **Göz Dostu**: Siyah-kırmızı renk paleti
- **Soft Gradients**: Yumuşak geçişler
- **Premium Look**: Profesyonel görünüm

### 🚀 Shared Hosting Optimizasyonu
- **cPanel Uyumlu**: Paylaşımlı hosting desteği
- **Performance Tuned**: Hız optimizasyonu
- **Resource Efficient**: Kaynak verimli kod

### 📢 Sosyal Medya Entegrasyonu
- **Telegram**: Anlık duyurular
- **Instagram**: Görsel içerik
- **Twitter**: Hızlı güncellemeler
- **TikTok**: Video içerik

## 📂 Proje Yapısı

### 🏠 Ana Site Bölümleri

- **Ana Sayfa**: Hero section ve genel istatistikler
- **Canlı Maçlar**: Şu anda yayında olan maçlar
- **7/24 Kanallar**: Kesintisiz spor kanalları
- **İletişim**: Sosyal medya bağlantıları

## 🚀 Kurulum

### 🎯 Kolay Kurulum Sihirbazı

DG SPORTS artık **3 adımda kolay kurulum** sistemi ile geliyor!

1. **Dosyaları Yükleyin**: Tüm dosyaları web sunucunuza yükleyin
2. **Setup Wizard'ı Açın**: `https://yoursite.com/setup.html` adresine gidin
3. **3 Adımda Tamamlayın**: Site ayarları → İçerik seçimi → Tamamla

### 1. Dosya Yükleme

Tüm dosyaları web sunucunuzun ana dizinine yükleyin:

```
/public_html/
├── index.html               # Ana site
├── setup.html              # 🆕 Kolay kurulum sihirbazı
├── admin/
│   ├── index.html          # Admin dashboard
│   ├── admin-dashboard.css # Admin stilleri
│   └── admin-dashboard.js  # Admin işlevleri
├── assets/
│   ├── css/
│   │   └── diziportal-styles.css
│   ├── js/
│   │   ├── diziportal-core.js    # 🆕 Inline player desteği
│   │   ├── diziportal-player.js
│   │   └── diziportal-data.js
│   └── images/
│       └── dg-sports-logo.png
└── .htaccess               # 🆕 Güvenlik optimizasyonlu
```

### 2. Kolay Kurulum (Önerilen)

1. **Setup Wizard'ı Açın**: `https://yoursite.com/setup.html`
2. **Site Bilgilerini Girin**: Başlık, açıklama, admin bilgileri
3. **Sosyal Medya**: Telegram, Instagram, Twitter, TikTok (opsiyonel)
4. **Örnek İçerik**: Varsayılan maç ve kanallar eklensin mi?
5. **Tamamla**: Site otomatik olarak hazır!

### 2. .htaccess Yapılandırması

`.htaccess` dosyası otomatik olarak yapılandırılmıştır:

```apache
# Gzip compression
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

### Admin Paneline Erişim

Admin paneli ana siteden tamamen ayrı olarak geliştirilmiştir:

- **URL**: `https://yoursite.com/admin/`
- **Kullanıcı Adı**: `admin`
- **Şifre**: `dgsports2024`

> ⚠️ **Güvenlik Uyarısı**: Kurulum sonrası `admin/admin-dashboard.js` dosyasında şifreyi değiştirin!

### Admin Dashboard Özellikleri

#### 🎯 Dashboard Ana Sayfa

- **Gerçek Zamanlı İstatistikler**: Toplam izleyici, canlı maç, aktif kanal sayıları
- **Grafik Analizleri**: Chart.js ile gelişmiş viewer analitikleri
- **Son Aktiviteler**: Sistem olayları ve kullanıcı etkileşimleri
- **Hızlı Erişim**: Tüm yönetim araçlarına tek tıkla erişim

#### 🏈 Canlı Maç Yönetimi

- **Gelişmiş Maç Ekleme**: Takım bilgileri, logoları, tarih, konum ve stream URL
- **Tablo Görünümü**: Filtrelenebilir ve aranabilir maç listesi
- **Durum Yönetimi**: Live, upcoming maç durumları
- **Viewer Tracking**: Gerçek zamanlı izleyici sayısı takibi
- **Bulk Operations**: Toplu maç işlemleri

#### 📺 Kanal Yönetimi

- **Kategorili Sistem**: Futbol, basketbol, genel spor ayrımı
- **Kanal Kalite Takibi**: Stream kalitesi ve uptime monitoring
- **Logo & Branding**: Kanal görsellerinin yönetimi
- **24/7 Stream Control**: Kesintisiz yayın kontrolü

#### 📊 Gelişmiş Analytics

- **Viewer Analytics**: Saatlik, günlük, aylık izleyici raporları
- **Content Performance**: En popüler maç ve kanallar
- **Geographic Data**: İzleyici coğrafi dağılımı
- **Device Analytics**: Mobil/desktop kullanım oranları

#### 👥 Kullanıcı Yönetimi

- **Admin Accounts**: Yönetici hesap kontrolü
- **Session Management**: Oturum güvenliği ve timeout
- **Access Control**: IP bazlı erişim kontrolü
- **Activity Logs**: Kullanıcı aktivite logları

#### 🎨 İçerik Yöneticisi

- **Site Content**: Tüm site metinlerini düzenleme
- **Media Management**: Görsel içerik yönetimi
- **SEO Settings**: Meta etiketler ve açıklamalar
- **Social Links**: Sosyal medya bağlantıları

#### 🔧 Sistem Ayarları

- **Site Configuration**: Genel site ayarları
- **Backup & Restore**: Veri yedekleme ve geri yükleme
- **System Logs**: Detaylı sistem logları
- **Security Settings**: Güvenlik konfigürasyonu

### Güvenlik Özellikleri

#### 🔒 Erişim Kontrolü

- **Separate Admin Interface**: Ana siteden tamamen ayrı admin paneli
- **IP Restriction**: Belirli IP adreslerinden erişim
- **Session Security**: Güvenli oturum yönetimi
- **Remember Me**: 30 günlük güvenli oturum seçeneği

#### 🛡️ Sistem Güvenliği

- **Authentication**: Güçlü kimlik doğrulama
- **CSRF Protection**: Cross-site request forgery koruması
- **XSS Prevention**: Cross-site scripting engelleme
- **Brute Force Protection**: Kaba kuvvet saldırısı koruması

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

### Breakpoint'ler

- **Mobile**: 320px - 768px
- **Tablet**: 768px - 1024px  
- **Desktop**: 1024px+

### Responsive Özellikler

- **Fluid Grid**: Esnek grid sistemi
- **Adaptive Images**: Otomatik görsel optimizasyonu
- **Touch-Friendly**: Dokunmatik kontroller
- **Mobile Navigation**: Hamburger menü

## 🎨 Özelleştirme

### Renk Teması

CSS değişkenleri ile kolay özelleştirme:

```css
:root {
    --primary-black: #0a0a0a;
    --secondary-black: #1a1a1a;
    --accent-red: #dc2626;
    --accent-red-dark: #991b1b;
    --accent-red-light: #ef4444;
}
```

### Logo ve Branding

`assets/images/` klasöründe logonuzu değiştirerek sitenizi kişiselleştirin.

## 🚀 Hosting Optimizasyonları

### Shared Hosting İçin

- **Gzip Compression**: Otomatik dosya sıkıştırma
- **Browser Caching**: Tarayıcı önbellekleme
- **Minified Assets**: Optimize edilmiş dosyalar
- **CDN Ready**: CDN entegrasyonu için hazır

### Performans İpuçları

- **Image Optimization**: Görselleri WebP formatında kullanın
- **Lazy Loading**: Görseller için lazy loading
- **Code Splitting**: JS kodunu parçalara bölün
- **Service Worker**: Offline support için

## 📊 Analytics

### Yerleşik Analytics

- **Real-time Viewers**: Gerçek zamanlı izleyici sayısı
- **Popular Content**: En çok izlenen içerik
- **User Engagement**: Kullanıcı etkileşim metrikleri
- **Performance Metrics**: Site performans verileri

### Google Analytics Entegrasyonu

```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=YOUR_GA_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'YOUR_GA_ID');
</script>
```

## 🔒 Güvenlik

### Güvenlik Önlemleri

- **XSS Protection**: Cross-site scripting koruması
- **CSRF Protection**: Cross-site request forgery koruması
- **SQL Injection**: Veritabanı güvenliği
- **File Upload Security**: Güvenli dosya yükleme

### Önerilen Güvenlik Ayarları

```apache
# .htaccess güvenlik ayarları
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|inc|bak|sql|conf)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

## 🐛 Sorun Giderme

### Yaygın Sorunlar

#### Video Oynatma Sorunu
- PlayerJS CDN'inin yüklendiğini kontrol edin
- CORS proxy ayarlarını kontrol edin
- Tarayıcı konsolunda hata mesajlarını inceleyin

#### Admin Panel Erişim Sorunu
- Doğru URL'yi kullandığınızdan emin olun (`/admin/`)
- Giriş bilgilerini kontrol edin
- IP kısıtlaması olup olmadığını kontrol edin

#### Responsive Tasarım Sorunu
- Viewport meta etiketinin doğru olduğunu kontrol edin
- CSS media query'lerini kontrol edin
- Tarayıcı önbelleğini temizleyin

### Debug Modu

Geliştirme sırasında console loglarını aktif edin:

```javascript
// diziportal-core.js içinde
const DEBUG_MODE = true;
```

## 📝 Changelog

### v1.0.0 (2024)
- ✅ **Yeni**: Ayrı admin dashboard sistemi
- ✅ **Yeni**: Gelişmiş analytics ve grafikler  
- ✅ **Yeni**: Güvenlik odaklı erişim kontrolü
- ✅ **Yeni**: Real-time viewer tracking
- ✅ **Yeni**: Comprehensive content management
- ✅ **İyileştirme**: Mobile-first responsive tasarım
- ✅ **İyileştirme**: PlayerJS entegrasyonu
- ✅ **İyileştirme**: CORS proxy sistemi
- ✅ **İyileştirme**: Shared hosting optimizasyonu

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasını inceleyin.

## 📞 İletişim

**Geliştirici**: DiziPortal.Com
**E-posta**: info@diziportal.com
**Website**: https://diziportal.com

---

<div align="center">
  <strong>DG SPORTS</strong> - Profesyonel Canlı Maç İzleme Platformu<br>
  Geliştirici: <a href="https://diziportal.com">DiziPortal.Com</a>
</div>
