# 📺 Box Sports - Premium Live Sports Streaming Platform

[![DiziPortal.Com](https://img.shields.io/badge/Developer-DiziPortal.Com-00ff88?style=for-the-badge)](https://diziportal.com)
[![Version](https://img.shields.io/badge/Version-1.0-blue?style=for-the-badge)](https://github.com)
[![License](https://img.shields.io/badge/License-Professional-gold?style=for-the-badge)](https://github.com)

**Box Sports** is a professional-grade live sports streaming platform developed by the **DiziPortal.Com** team. This comprehensive solution features a dark modern UI, advanced HLS video streaming, and a powerful admin management system.

## 🌟 Key Features

### 🎬 Advanced Video Streaming
- **HLS.js Integration**: Professional-grade HTTP Live Streaming support
- **Cross-Platform Compatibility**: Works on all modern browsers and devices
- **Adaptive Quality**: Automatic quality adjustment based on connection speed
- **Low Latency**: Optimized for real-time sports streaming
- **Error Recovery**: Robust error handling and automatic recovery
- **CORS Handling**: Built-in cross-origin request management

### 🎨 Modern User Interface
- **Sports Theme**: Dynamic sports-themed dark design with floating elements
- **Responsive Design**: Perfect viewing on desktop, tablet, and mobile
- **Hamburger Menu**: Mobile-optimized navigation with smooth animations
- **Gradient Aesthetics**: Beautiful color gradients throughout the interface
- **Smooth Animations**: Polished transitions and hover effects
- **Professional Layout**: Clean, intuitive user experience

### ⚙️ Admin Management System
- **Secure Authentication**: Login system with session management
- **Match Management**: Add, edit, and delete live matches
- **HLS Link Management**: Easy integration of streaming URLs
- **Real-time Updates**: Instant synchronization between admin and public views
- **Statistics Dashboard**: Track matches and streaming status
- **User Management**: Change username and password
- **Mobile Optimized**: Responsive admin interface

### 🔧 Technical Excellence
- **DiziPortal Branding**: Consistent DiziPortal.Com branding throughout
- **Prefixed CSS Classes**: All CSS classes use `diziportal-` prefix
- **Namespaced JavaScript**: All functions use `diziportal` naming convention
- **LocalStorage Integration**: Persistent data storage for matches
- **Cross-tab Synchronization**: Real-time updates across browser tabs

## 📁 Project Structure

```
box-sports/
├── index.html              # Main streaming interface
├── login.html              # Secure admin login page
├── admin.html              # Administrative panel
├── hls-player.js           # Advanced HLS player module
└── README.md              # This documentation
```

## 🚀 Quick Start

### 1. Download Files
Clone or download all project files to your web server directory.

### 2. Setup Web Server
Ensure you have a web server running (Apache, Nginx, or local development server).

### 3. Access the Platform
- **Main Site**: Open `index.html` in your browser
- **Admin Panel**: Click "Admin" button or navigate to `login.html`

### 4. Admin Login
**Default Credentials:**
- **Username**: `admin`
- **Password**: `diziportal2025`

⚠️ **Important**: Change these credentials immediately after first login!

### 5. Add Your First Match
1. Log into the admin panel
2. Fill in match details:
   - **Match Name**: e.g., "⚽ Real Madrid vs Barcelona - Champions League"
   - **Match Time**: Set the scheduled time
   - **HLS Link**: Add your streaming URL (e.g., `https://example.com/stream.m3u8`)
3. Click "Maç Ekle" to add the match
4. Return to the main site to see your match listed

### 6. Security Setup
1. **Change Admin Credentials**: Click "Şifre Değiştir" in admin panel
2. **Set Strong Password**: Use at least 6 characters
3. **Regular Updates**: Change credentials periodically for security

## 🎯 Usage Guide

### For Viewers
1. **Browse Matches**: View available live matches in the sidebar
2. **Select Match**: Click on any match to start streaming
3. **Watch Live**: Enjoy high-quality live sports streaming
4. **Quality Control**: Player automatically adjusts quality based on connection

### For Administrators
1. **Access Admin**: Use the admin panel to manage content
2. **Add Matches**: Create new live match entries
3. **Manage Links**: Update or remove streaming URLs
4. **Monitor Stats**: Track total matches and active streams

## 🔧 Configuration

### HLS Player Settings
The advanced HLS player can be configured in `hls-player.js`:

```javascript
const options = {
    debug: false,
    lowLatencyMode: true,
    enableWorker: true,
    maxBufferLength: 30,
    // ... more options
};
```

### CORS Configuration
For production use, configure CORS headers on your streaming server:

```apache
# Apache .htaccess
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type"
```

## 🎨 Customization

### Branding
All branding elements are clearly marked with `DiziPortal.Com`. To customize:

1. **CSS Classes**: All use `diziportal-` prefix for easy identification
2. **JavaScript Functions**: All use `diziportal` prefix
3. **Console Messages**: All include DiziPortal.Com branding
4. **Visual Elements**: Update colors, logos, and text as needed

### Styling
The platform uses CSS custom properties for easy theming:

```css
/* Main brand colors */
--diziportal-primary: #00ff88;
--diziportal-secondary: #00d4ff;
--diziportal-accent: #ff6b6b;
```

## 📱 Browser Support

- ✅ **Chrome/Chromium** 80+
- ✅ **Firefox** 75+
- ✅ **Safari** 13+
- ✅ **Edge** 80+
- ✅ **Mobile Browsers** (iOS Safari, Chrome Mobile)

## 🔒 Security Features

- **Input Validation**: All user inputs are validated
- **CORS Handling**: Proper cross-origin request management
- **Error Boundaries**: Graceful error handling throughout
- **Secure Streaming**: HTTPS support for encrypted streaming

## 🚀 Performance

- **Optimized Loading**: Efficient resource loading and caching
- **Adaptive Streaming**: Dynamic quality adjustment
- **Memory Management**: Proper cleanup of video resources
- **Low Latency**: Optimized for real-time streaming

## 📊 Features Matrix

| Feature | Status | Description |
|---------|--------|-------------|
| 🎬 HLS Streaming | ✅ | Professional HLS.js integration |
| 📱 Responsive | ✅ | Works on all device sizes |
| ⚙️ Admin Panel | ✅ | Complete match management |
| 🔄 Real-time Updates | ✅ | Live synchronization |
| 🎨 Dark Theme | ✅ | Modern dark interface |
| 📊 Statistics | ✅ | Match and streaming stats |
| 🔧 Error Recovery | ✅ | Automatic error handling |
| 🌐 CORS Support | ✅ | Cross-origin streaming |

## 🆘 Troubleshooting

### Common Issues

**Video Not Playing**
- Check HLS URL format (should end with .m3u8)
- Verify CORS headers on streaming server
- Test URL in browser directly

**Admin Panel Not Saving**
- Check browser localStorage support
- Clear browser cache and cookies
- Verify JavaScript is enabled

**Cross-Origin Errors**
- Configure CORS headers on streaming server
- Use HTTPS for both site and streams
- Check browser console for specific errors

## 🤝 Support

For technical support and customization services:

- **Website**: [DiziPortal.Com](https://diziportal.com)
- **Email**: support@diziportal.com
- **Documentation**: Check this README for detailed information

## 📄 License

This project is developed by **DiziPortal.Com** and is licensed for professional use. All rights reserved.

---

**© 2025 DiziPortal.Com - Box Sports | Professional Sports Streaming Platform**

*Developed with ❤️ by the DiziPortal.Com Development Team*
