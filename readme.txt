=== RTCL Offload Media ===
Contributors: techlabpro1, mamunnu
Donate link:
Tags: media offload, image offload, cloud storage, cloudflare r2, wasabi, cdn
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Offload WordPress media files to external cloud storage and serve them efficiently via CDN.

== Description ==

**RTCL Offload Media** allows you to offload WordPress media files (images, videos, and other uploads) to supported cloud storage services, helping reduce server load and improve website performance.

The plugin seamlessly integrates with the WordPress Media Library and rewrites media URLs so files are served directly from remote storage or CDN.

### Supported Storage Providers
- **Cloudflare R2**
- **Wasabi**

### Key Features
- Offload WordPress media uploads to cloud storage
- Support for Cloudflare R2 and Wasabi
- Automatic media URL rewriting
- Reduce server disk usage
- Improve website performance
- Works with the WordPress Media Library
- Lightweight and developer-friendly

Ideal for high-traffic websites, directory platforms, and media-heavy WordPress sites.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/rtcl-offload-media` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Settings → RTCL Offload Media** to configure your storage provider.

== Frequently Asked Questions ==

= Which cloud storage services are supported? =
Currently, the plugin supports Cloudflare R2 and Wasabi.

= Does it work with existing media files? =
Existing media files can be offloaded based on your configuration.

= Will this break my Media Library? =
No. The Media Library continues to function normally.

= Does it support CDN delivery? =
Yes. Media files can be served via CDN URLs.

== Changelog ==

= 1.0.0 =
- Initial release
- Media offload support for Cloudflare R2 and Wasabi
- Automatic media URL rewriting
- Performance improvements

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Credits ==

Cloudflare® and Wasabi® are trademarks of their respective owners.
This plugin is not affiliated with or endorsed by these companies.