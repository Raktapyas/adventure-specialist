---
paths:
  - app/Services/MediaUploader.php
---

# Services

## Media kind derives from sniffed MIME; caps are per-kind
media.type ('image'|'video') is derived in Media::creating from mime_type; MediaUploader also sets it explicitly. Normalize application/mp4 → video/mp4 before allowlist checks (some finfo builds and browsers report MP4 as application/mp4). getimagesize() runs only for jpeg/png/webp/gif — AVIF and videos must skip it or they get wrongly rejected. Size caps: config('media.max_image_bytes') = 5 MB, max_video_bytes = 50 MB. FileUpload acceptedFileTypes must include application/mp4 alongside config mimes: Filament's mimetypes rule validates the CLIENT-declared MIME and Symfony guesses .mp4 as application/mp4.
