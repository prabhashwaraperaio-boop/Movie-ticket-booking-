# CINEMORA — Visual Assets & Media Manifest

All visual assets in **CINEMORA** are 100% self-contained local files stored inside `assets/images/`. Each movie poster, cinema location banner, and hero backdrop is provided both as a high-resolution photorealistic `.jpg` and an embedded `.svg` counterpart, with zero external CDNs or remote image URLs.

---

## 1. Brand & Hero Assets

| Asset Path | Format / Aspect | Purpose & Usage |
| :--- | :--- | :--- |
| `assets/images/logo.svg` | `220 × 44` (Vector SVG) | Primary CINEMORA gold-and-crimson wordmark and film-aperture emblem used in the public navigation bar, footer, printable e-ticket, and administrator sidebar. |
| `assets/images/hero/hero-banner.jpg` (`hero-banner.svg`) | `16:9` Photorealistic JPEG & SVG | Wide-angle luxury auditorium photograph with a golden-and-crimson projector light beam cutting across tiered leather seats toward a glowing panoramic screen. |

---

## 2. High-Resolution Theatrical Movie Posters (`assets/images/movies/`)

`resolve_poster_url()` in `includes/functions.php` automatically resolves any movie poster reference (whether `.jpg` or legacy `.svg`) to the high-resolution `.jpg` asset with cache-busting `?v=` timestamps:

| Asset Path | Assigned Sample Movie | Visual Scene & Genre |
| :--- | :--- | :--- |
| `assets/images/movies/movie-1.jpg` (`movie-1.svg`) | **The Crimson Monsoon** | Action / Thriller — Stormy night at Colombo container harbor in monsoon rain with crimson emergency flares, lightning, cargo ships, and a lone investigator. |
| `assets/images/movies/movie-2.jpg` (`movie-2.svg`) | **Echoes of Sigiriya** | Historical Drama / Mystery — Majestic Sigiriya Lion Rock rising through golden twilight jungle mist above torch-bearing explorers in a subterranean fresco chamber. |
| `assets/images/movies/movie-3.jpg` (`movie-3.svg`) | **Starlight Over Colombo** | Romance / Drama — Starry evening on a rooftop terrace overlooking the Indian Ocean and glowing Colombo skyline with a cellist and warm string lights. |
| `assets/images/movies/movie-4.jpg` (`movie-4.svg`) | **Orbital Velocity** | Sci-Fi / Adventure — Deep-space exploration vessel firing blue ion thrusters over the icy surface of Europa with Jupiter reflected in an astronaut's visor. |
| `assets/images/movies/movie-5.jpg` (`movie-5.svg`) | **Shadows in the Tea Mist** | Mystery / Suspense — 19th-century colonial stone manor in Nuwara Eliya surrounded by emerald tea hills in silver mist and a detective carrying a brass lantern. |
| `assets/images/movies/movie-6.jpg` (`movie-6.svg`) | **The Last Tuk-Tuk Rally** | Comedy / Family — Custom-painted red-and-gold Sri Lankan three-wheeler tuk-tuk racing along a palm-lined coastal highway at sunset with three cheering friends. |
| `assets/images/movies/movie-7.jpg` (`movie-7.svg`) | **Valkyrie of the Indian Ocean** | Action / Adventure — Fearless female fleet navigator standing on a carved dragon prow cutting through crashing turquoise Indian Ocean waves ahead of an armada. |
| `assets/images/movies/movie-8.jpg` (`movie-8.svg`) | **Symphony of the Lotus Tower** | Musical / Drama — Colombo Lotus Tower illuminated in laser beams above a live philharmonic orchestra and Kandyan drummers on a glass sky-deck. |
| `assets/images/movies/poster-fallback.svg` | Automatic Fallback Poster | Used when a movie record has no uploaded poster file. |

---

## 3. Photorealistic Cinema Location Banners (`assets/images/cinemas/`)

| Asset Path | Assigned Cinema Location | Visual Scene |
| :--- | :--- | :--- |
| `assets/images/cinemas/cinema-1.jpg` (`cinema-1.svg`) | **Cinemora Grand Colombo City** | Flagship IMAX laser auditorium with tiered crimson and black leather seating, warm golden LED aisle step lighting, and a giant curved projection screen. |
| `assets/images/cinemas/cinema-2.jpg` (`cinema-2.svg`) | **Cinemora Royale Kandy Hills** | Regal stadium auditorium with burgundy velvet seating, warm brass handrails, and golden chandelier lighting. |
| `assets/images/cinemas/cinema-3.jpg` (`cinema-3.svg`) | **Cinemora Marina Galle Bay** | Boutique coastal VIP lounge with motorized navy leather couple recliners and warm brass table lamps at each seat. |
| `assets/images/cinemas/cinema-fallback.svg` | Automatic Fallback Banner | Used when a cinema location has no uploaded banner image. |

---

## 4. Uploaded Media Security (`uploads/`)

- Administrator-uploaded movie posters are stored in `uploads/movies/` and cinema location banners in `uploads/cinemas/`.
- Both upload flows verify:
  1. Upload error status and maximum file size (`<= 3 MB`).
  2. Real MIME type via PHP `finfo_file()` (`image/jpeg`, `image/png`, `image/webp`) and image dimensions via `getimagesize()`.
  3. Cryptographically random filenames (`bin2hex(random_bytes(12))`) to prevent path traversal or filename collisions.
- `uploads/.htaccess` disables PHP/script execution inside the `uploads/` directory tree.
