You are an expert Laravel developer. Build a complete production-ready Laravel
sports streaming website for domain www.evaulthub.com with admin panel,
analytics, multi-server support, and maximum AdSense revenue optimization.
Follow every instruction below with zero shortcuts.

═══════════════════════════════════════════════
1. PROJECT OVERVIEW
   ═══════════════════════════════════════════════
   Name: EVaultHub - Live Sports Streaming
   Domain: www.evaulthub.com
   Framework: Laravel (latest stable)
   Language: English
   Stack: Laravel + MySQL + Tailwind CSS (CDN) + Blade
   Primary Goal: Maximum Google AdSense RPM ($3+)

═══════════════════════════════════════════════
2. DATABASE CONFIGURATION (.env)
   ═══════════════════════════════════════════════
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=codexwww
   DB_USERNAME=codexwww
   DB_PASSWORD=2RYTaDdpFHHSwBFb

═══════════════════════════════════════════════
3. DATABASE SCHEMA — RUN THESE MIGRATIONS
   ═══════════════════════════════════════════════
   Generate Laravel migrations for the following schema:

--- TABLE: servers ---
CREATE TABLE servers (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,           -- "Server 1", "Server 2"
json_url TEXT NOT NULL,               -- remote JSON URL
is_active TINYINT(1) DEFAULT 1,
created_at TIMESTAMP,
updated_at TIMESTAMP
);

INSERT INTO servers (name, json_url) VALUES
('Server 1', 'https://raw.githubusercontent.com/albinchristo04/ptv/refs/heads/main/futbollibre.json'),
('Server 2', 'https://raw.githubusercontent.com/albinchristo04/ptv/refs/heads/main/events_with_m3u8.json');

--- TABLE: matches ---
CREATE TABLE matches (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(500) NOT NULL,          -- Full match title e.g. "Liga MX: Puebla vs América"
league VARCHAR(255),                  -- Extracted league name
team_home VARCHAR(255),
team_away VARCHAR(255),
match_datetime DATETIME,              -- Combined date + time
country VARCHAR(100),
server_id INT UNSIGNED,
fingerprint VARCHAR(64) UNIQUE,       -- MD5 of (title+date) for dedup
status ENUM('upcoming','live','finished') DEFAULT 'upcoming',
is_featured TINYINT(1) DEFAULT 0,
deleted_at TIMESTAMP NULL,            -- soft delete
created_at TIMESTAMP,
updated_at TIMESTAMP,
FOREIGN KEY (server_id) REFERENCES servers(id)
);

--- TABLE: match_streams ---
CREATE TABLE match_streams (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
match_id INT UNSIGNED NOT NULL,
channel_name VARCHAR(255),            -- "Win Sports+", "TUDN USA", "Channel 1"
iframe_url TEXT NOT NULL,             -- decoded_iframe_url / m3u8 URL
stream_type ENUM('iframe','m3u8') DEFAULT 'iframe',
sort_order INT DEFAULT 0,
created_at TIMESTAMP,
updated_at TIMESTAMP,
FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
);

--- TABLE: match_views ---
CREATE TABLE match_views (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
match_id INT UNSIGNED NOT NULL,       -- keep even after match deleted
server_id INT UNSIGNED,
match_title VARCHAR(500),             -- denormalized for analytics after deletion
viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ip_address VARCHAR(45),
user_agent VARCHAR(500),
INDEX idx_viewed_at (viewed_at),
INDEX idx_match_id (match_id)
);
-- NOTE: No FK on match_id so views survive after match deletion

--- TABLE: admin_users ---
CREATE TABLE admin_users (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(100) UNIQUE NOT NULL,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP
);
-- Seed: username=admin, password=bcrypt('admin123')

═══════════════════════════════════════════════
4. JSON DATA SOURCES
   ═══════════════════════════════════════════════
   Server 1: https://raw.githubusercontent.com/albinchristo04/ptv/refs/heads/main/futbollibre.json
   Server 2: https://raw.githubusercontent.com/albinchristo04/ptv/refs/heads/main/events_with_m3u8.json

JSON Parsing Rules:
- diary_description = full match title (format: "League: Team A vs Team B")
- diary_hour = time string "HH:MM:SS"
- date_diary = date string "YYYY-MM-DD"
- attributes.embeds.data[] = array of stream channels
    * embed_name = channel label
    * decoded_iframe_url = the ACTUAL iframe src URL to embed
- attributes.country.data.attributes.name = country name

For Server 2 (m3u8 JSON), adapt parsing to its structure.
If stream URL ends in .m3u8, set stream_type='m3u8' and use HLS.js to play it.
If stream URL is a regular URL, set stream_type='iframe' and use <iframe>.

Fingerprint generation:
MD5( strtolower(trim(title)) + date_diary )
Use this to prevent duplicate imports.

═══════════════════════════════════════════════
5. PUBLIC WEBSITE — PAGES
   ═══════════════════════════════════════════════

─────────────────────────────────────────────
5A. HOMEPAGE  (/)
─────────────────────────────────────────────
Title: "Watch Live Football Streams Online Free HD | EVaultHub"
Meta desc: "Stream live football, soccer, Liga MX, Premier League,
Champions League, LaLiga, Serie A free in HD. Watch today's live
matches on EVaultHub."
Meta keywords: "live football stream free, watch soccer online free HD,
live sports streaming, watch football match online, free live soccer stream"

SERVER TABS (critical — do NOT merge servers):
Show two tabs at top of homepage:
[ 📡 Server 1 ]  [ 📡 Server 2 ]
- Default selected: Server 1
- Each tab shows ONLY that server's matches from DB
- Tab switching is client-side (JS show/hide divs, no page reload)
- Each tab has its own match grid

Match Cards Grid:
- 3 columns desktop, 2 tablet, 1 mobile
- Each card shows:
    * League badge (colored pill)
    * Team Home vs Team Away (large bold)
    * Match datetime (12hr format)
    * Country flag emoji
    * 🔴 LIVE (pulsing dot) if match_datetime <= now <= match_datetime + 2hrs
    * UPCOMING (grey) if in future
    * FINISHED (muted) if older than 2hrs
    * "N streams" badge in corner
- Card links to /watch/{slug}
- Hover: red border glow, slight scale up

League Filter:
Above cards, show league filter pills extracted from DB
Clicking a league filters cards in that tab only (JS filter)

AD PLACEMENT HOMEPAGE:
1. AD-LEADERBOARD (728x90) — immediately below navbar, above tabs
2. AD-RESPONSIVE — after every 6th match card (injected in loop)
3. AD-MOBILE-STICKY — fixed bottom on mobile screens only

─────────────────────────────────────────────
5B. MATCH DETAIL / PLAYER PAGE  (/watch/{slug})
─────────────────────────────────────────────
Slug: Str::slug(title . '-' . date) stored in matches table as `slug` column.
Add `slug VARCHAR(500) UNIQUE` to matches table.

Title: "Watch {Match Title} Live Stream Free HD | EVaultHub"
Meta desc: "Watch {Match Title} live stream free in HD. Stream {League}
online free on EVaultHub. No signup required. HD quality."
Meta keywords: "watch {team_home} vs {team_away} live, {league} live stream,
{match title} free stream online HD"
Meta og:tags: title, description, url, image (default banner)
Canonical URL
Schema.org JSON-LD SportsEvent structured data
Breadcrumb Schema: Home > {League} > {Match Title}

RECORD VIEW:
On every page load, insert into match_views:
(match_id, server_id, match_title, viewed_at, ip_address, user_agent)
Use middleware or controller — throttle: max 1 view per IP per match per hour

──── AD UNIT 1 ────
Show AD-LEADERBOARD (728x90) + AD-SMALL (300x100) side by side above player.
On mobile: stack them vertically.

──── PLAYER SECTION ────
Container: max-width 960px, centered, dark bg #000, border-radius 14px,
subtle red glow box-shadow, 16:9 aspect ratio enforced with padding-top trick.

BEFORE CLICK (lazy load):
Show a dark overlay with:
* Sports thumbnail background (CSS gradient if no image)
* Large circular play button (▶ icon, white, 80px)
* Text: "Click to Start Streaming"
* Match title overlay at bottom

ON CLICK:
Hide overlay, show player iframe/video.
No auto-refresh. Autoplay=1 in iframe src.

CHANNEL TABS (if multiple streams):
Show tab buttons ABOVE player:
"📺 {channel_name}" for each stream
Active tab: red background, bold
Tab click: swap iframe src (JS, no reload)

IFRAME embed (stream_type = iframe):
<iframe src="{iframe_url}?autoplay=1" width="100%" height="100%"
frameborder="0" scrolling="no" allowfullscreen
allow="autoplay; encrypted-media; picture-in-picture"
style="position:absolute;top:0;left:0;width:100%;height:100%">
</iframe>

M3U8 embed (stream_type = m3u8):
Use HLS.js CDN to play m3u8 URLs in a <video> tag.
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
Auto-initialize HLS on the video element with the m3u8 URL.

Under player:
Small italic text: "Streaming provided by external source.
EVaultHub does not host any content."

──── AD UNIT 2 ────
AD-RESPONSIVE (full width) immediately below player container.

──── MATCH INFO SECTION ────
Card layout below player (dark card, icon + label style):
🏆 Competition: {league}
📡 Server: {server name}
📅 Match Date: {formatted datetime}
🌍 Country: {country}
📶 Stream Quality: HD (static label)
📝 About this match: 2-3 sentence SEO paragraph:
"Watch {title} live stream free in HD on EVaultHub.
This {league} match is available to stream online free
without any subscription. Enjoy live {league} football
streaming in high definition."

──── SHARE BUTTONS ROW ────
Title: "Share this Match"
Buttons (rounded, colored, icon + text, hover scale):
🟢 WhatsApp  → https://wa.me/?text={encoded url+title}
🔵 Telegram  → https://t.me/share/url?url={url}&text={title}
🐦 Twitter/X → https://twitter.com/intent/tweet?url={url}&text={title}
🔵 Facebook  → https://www.facebook.com/sharer/sharer.php?u={url}
🔗 Copy Link → JS clipboard copy, button text changes to "✅ Copied!"
All open in _blank with rel="noopener noreferrer"

──── JOIN TELEGRAM SECTION ────
Prominent dark card with blue accent:
[Telegram Icon - large]
"📲 Get Instant Match Alerts & New Streams"
Subtext: "Join our Telegram channel for live stream notifications,
new match links, and real-time updates."
Large button: "⚡ Join Now on Telegram" (blue, rounded-full, hover glow)
Link: https://t.me/+brOxYHl33qljZTQ1  (target="_blank")

──── HOW TO WATCH SECTION ────
H2: "How to Watch {Match Title} Live Stream Free Online"
150+ word paragraph with natural keyword usage:
"Looking to watch {title} live stream free online? EVaultHub offers
the best free sports streaming experience in HD quality. To watch
{team_home} vs {team_away} live, simply click the play button above
and select your preferred stream server. No registration or subscription
required to watch {league} matches online free. Our streams are updated
regularly to ensure you never miss a moment of live football action..."
(Generate full paragraph dynamically with match variables)

──── RELATED MATCHES SECTION ────
Title: "You May Also Like"
Query: Same server_id, exclude current match, order by match_datetime DESC, LIMIT 8
Horizontal scroll row (overflow-x: auto, scroll snap).
Each card (min-width 220px):
* Dark card
* League badge
* Title (2 line clamp)
* Date badge
* Hover zoom (scale 1.05)
* Link to /watch/{slug}

──── AD UNIT 5 ────
AD-RESPONSIVE at very bottom of page before footer.

─────────────────────────────────────────────
5C. LEAGUE PAGE  (/league/{league-slug})
─────────────────────────────────────────────
Shows all DB matches for that league (not deleted).
Title: "Watch {League} Live Streams Free Online | EVaultHub"
AD-LEADERBOARD at top, AD-RESPONSIVE in middle.

─────────────────────────────────────────────
5D. SCHEDULE PAGE  (/schedule)
─────────────────────────────────────────────
All matches from DB grouped by league, all servers combined.
Title: "Today's Live Football Schedule & Free Streams | EVaultHub"
AD-LEADERBOARD top, AD-RESPONSIVE middle.

─────────────────────────────────────────────
5E. SITEMAP  (/sitemap.xml)
─────────────────────────────────────────────
Dynamic XML sitemap listing:
/ (homepage)
/schedule
All /league/{slug} pages
All /watch/{slug} pages (not deleted)
Return with Content-Type: application/xml

─────────────────────────────────────────────
5F. STATIC PAGES
─────────────────────────────────────────────
/privacy-policy — Full professional privacy policy mentioning:
Google AdSense cookies, analytics, data collection, third-party embeds.
Required for AdSense approval.
/dmca — DMCA notice: EVaultHub is a link aggregator, not a content host.
Takedown contact: dmca@evaulthub.com
/contact — Contact page: contact@evaulthub.com

═══════════════════════════════════════════════
6. ADMIN PANEL  (/admin/*)
   ═══════════════════════════════════════════════
   Route prefix: /admin
   Auth: Simple session-based login (no Laravel Breeze needed).
   Use admin_users table. Middleware: AdminAuth.

Routes:
GET  /admin/login
POST /admin/login
GET  /admin/logout
GET  /admin/dashboard
GET  /admin/import
POST /admin/import/fetch
POST /admin/import/store
GET  /admin/matches
GET  /admin/matches/create
POST /admin/matches
GET  /admin/matches/{id}/edit
PUT  /admin/matches/{id}
DELETE /admin/matches/{id}
GET  /admin/analytics

Admin Layout:
Dark sidebar with links: Dashboard, Import Matches, Manage Matches, Analytics, Logout
Top bar with admin username

─────────────────────────────────────────────
6A. DASHBOARD (/admin/dashboard)
─────────────────────────────────────────────
Stats cards row:
* Total Matches in DB
* Total Views Today
* Total Matches per Server (Server 1: X | Server 2: Y)
* Active Streams

─────────────────────────────────────────────
6B. IMPORT MATCHES (/admin/import)
─────────────────────────────────────────────
STEP 1 — SELECT SERVER:
Dropdown or radio: "Select Server to Import From"
Options: Server 1, Server 2 (from servers table)
Button: "Fetch Matches" (submits POST to /admin/import/fetch)

STEP 2 — PREVIEW MATCHES (after fetch):
Show a table of matches fetched from that server's JSON URL.
Columns: ☐ (checkbox) | Match Title | League | Date/Time | Streams Count | Status
"Status" column shows either:
✅ New (not in DB yet, based on fingerprint check)
⚠️ Already Imported (fingerprint exists in DB)
Top row: [ ☐ Select All ] [ Select New Only ] buttons
Submit button: "Import Selected Matches"

STEP 3 — IMPORT LOGIC (POST /admin/import/store):
For each selected match:
1. Generate fingerprint = MD5(strtolower(title) + date_diary)
2. Check if fingerprint exists in matches table
3. If NOT exists:
- Insert into matches (title, league, team_home, team_away,
match_datetime, country, server_id, fingerprint, slug)
- Insert each embed into match_streams
4. If EXISTS:
- Skip (already imported)
Show result: "X matches imported, Y skipped (already exist)"

─────────────────────────────────────────────
6C. MANAGE MATCHES (/admin/matches)
─────────────────────────────────────────────
Filter bar:
* Filter by Server (dropdown)
* Filter by League (dropdown)
* Search by title
* Filter by Date

Paginated table (20 per page):
Columns: ID | Title | League | Server | DateTime | Streams | Status | Actions
Actions: [Edit] [Delete]
Deleted (soft) rows shown in muted red with [Restore] option

CREATE NEW MATCH (/admin/matches/create):
Form fields:
* Title (text)
* League (text)
* Team Home (text)
* Team Away (text)
* Match Date (date picker)
* Match Time (time picker)
* Country (text)
* Server (dropdown from servers table)
* Streams (dynamic repeater):
[ + Add Stream ] button
Each stream row: Channel Name | Iframe URL | Stream Type (iframe/m3u8) | [Remove]
Submit: "Create Match"

EDIT MATCH (/admin/matches/{id}/edit):
Same form pre-filled with existing data.
Streams repeater shows existing streams, allow add/remove.
Submit: "Save Changes"

SOFT DELETE:
Set deleted_at timestamp. Match disappears from public site.
Views data preserved in match_views (NO FK cascade delete).

─────────────────────────────────────────────
6D. ANALYTICS (/admin/analytics)
─────────────────────────────────────────────
All data comes from match_views table (survives match deletion).

TOP STATS CARDS ROW:
* Today's Views (COUNT where DATE(viewed_at) = today)
* Yesterday's Views
* Last 7 Days Total
* All Time Total

SERVER BREAKDOWN TABLE:
Server Name | Views Today | Views Yesterday | Views 7 Days | Total Views
(JOIN match_views.server_id to servers)

TOP 10 MATCHES TABLE (last 7 days):
Rank | Match Title | Server | Views (7d) | Total Views
(Use match_title from match_views for deleted match support)
Highlight deleted matches with a small "(deleted)" grey badge

VIEWS CHART:
Simple bar chart using Chart.js CDN
Last 7 days — x-axis: dates, y-axis: view count
Two datasets: Server 1 (blue), Server 2 (red)

═══════════════════════════════════════════════
7. API ENDPOINTS (mobile-ready, no auth)
   ═══════════════════════════════════════════════
   Routes prefix: /api (in routes/api.php)

GET /api/matches?server={server_id}&date={YYYY-MM-DD}
Response JSON:
{
"success": true,
"server": "Server 1",
"matches": [
{
"id": 1,
"title": "Liga MX: Puebla vs América",
"league": "Liga MX",
"team_home": "Puebla",
"team_away": "América",
"match_datetime": "2026-02-20T22:05:00",
"country": "México",
"status": "live",
"slug": "liga-mx-puebla-vs-america-2026-02-20",
"streams_count": 3,
"url": "https://www.evaulthub.com/watch/liga-mx-puebla-vs-america-2026-02-20"
}
]
}

GET /api/match/{id}
Response JSON:
{
"success": true,
"match": { ...all match fields... },
"streams": [
{ "channel_name": "Channel 1", "iframe_url": "...", "stream_type": "iframe" }
]
}

═══════════════════════════════════════════════
8. ADSENSE AD UNITS — EXACT IMPLEMENTATION
   ═══════════════════════════════════════════════
   Load AdSense script ONCE in <head> of main layout:
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7025462814384100" crossorigin="anonymous"></script>

Also add in <head>:
<link rel="preconnect" href="https://pagead2.googlesyndication.com">
<link rel="dns-prefetch" href="//pagead2.googlesyndication.com">

Create these as Blade components (resources/views/components/):

── x-ad-leaderboard.blade.php ── (728x90)
<div class="ad-container flex justify-center my-3" aria-label="Advertisement">
  <ins class="adsbygoogle"
       style="display:inline-block;width:728px;height:90px"
       data-ad-client="ca-pub-7025462814384100"
       data-ad-slot="4498534581"></ins>
  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>

── x-ad-responsive.blade.php ── (responsive auto)
<div class="ad-container my-4" aria-label="Advertisement">
  <ins class="adsbygoogle"
       style="display:block"
       data-ad-client="ca-pub-7025462814384100"
       data-ad-slot="4164344667"
       data-ad-format="auto"
       data-full-width-responsive="true"></ins>
  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>

── x-ad-mobile.blade.php ── (300x50 sticky bottom mobile)
<div class="ad-container fixed bottom-0 left-1/2 -translate-x-1/2 z-50 
            block md:hidden py-1" aria-label="Advertisement">
  <ins class="adsbygoogle"
       style="display:inline-block;width:300px;height:50px"
       data-ad-client="ca-pub-7025462814384100"
       data-ad-slot="9766356837"></ins>
  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>

── x-ad-small.blade.php ── (300x100)
<div class="ad-container flex justify-center my-3" aria-label="Advertisement">
  <ins class="adsbygoogle"
       style="display:inline-block;width:300px;height:100px"
       data-ad-client="ca-pub-7025462814384100"
       data-ad-slot="3480966381"></ins>
  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>

── x-ad-bottom.blade.php ── (responsive auto — below player / bottom)
<div class="ad-container my-4" aria-label="Advertisement">
  <ins class="adsbygoogle"
       style="display:block"
       data-ad-client="ca-pub-7025462814384100"
       data-ad-slot="4696448973"
       data-ad-format="auto"
       data-full-width-responsive="true"></ins>
  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>

AD PLACEMENT RULES:
Homepage: leaderboard top → responsive every 6 cards → sticky mobile bottom
Match page: leaderboard+small above player → responsive below player →
responsive at page bottom → sticky mobile bottom
League/Schedule: leaderboard top → responsive middle
Admin pages: NO ads (waste of impressions + bad UX)

═══════════════════════════════════════════════
9. SEO — FULL REQUIREMENTS
   ═══════════════════════════════════════════════
   Every page:
  <title> — unique, keyword-rich
  <meta name="description"> — unique 150-160 chars
  <meta name="keywords"> — relevant high-CPC sports keywords
  <link rel="canonical"> — self-referencing canonical URL
  Open Graph: og:title, og:description, og:url, og:image, og:type
  Twitter Card: twitter:card, twitter:title, twitter:description

Match pages additional:
  <meta name="news_keywords" content="{team_home},{team_away},{league},live stream">
  Schema.org SportsEvent JSON-LD
  Schema.org BreadcrumbList JSON-LD

Global:
robots.txt → allow all, reference /sitemap.xml
/sitemap.xml → dynamic, all public pages
H1 on every page with primary keyword
No duplicate content

═══════════════════════════════════════════════
10. DESIGN SYSTEM
    ═══════════════════════════════════════════════
    Theme: Dark sports theme
    Colors:
    bg-primary:   #0a0a0f
    bg-card:      #12121e
    bg-elevated:  #1a1a2e
    accent-red:   #e94560
    accent-blue:  #0078d7
    text-primary: #ffffff
    text-muted:   #9ca3af

Typography: System font stack, bold headings

Logo: "⚡ EVaultHub" — lightning bolt in #e94560, rest white, font-bold text-2xl

Navbar (sticky, dark, z-50):
[⚡ EVaultHub] ... [Home] [Schedule] [🔍]
Mobile: hamburger menu (Alpine.js or vanilla JS toggle)

Footer:
Dark bg #080810, 3 columns:
Col 1: Logo + tagline + SEO paragraph (150 words, high-CPC keywords):
"EVaultHub is your ultimate destination for free live sports streaming
online in HD quality. Watch live football, soccer, Liga MX, Premier
League, UEFA Champions League, LaLiga, Serie A, Bundesliga, Ligue 1,
Copa Libertadores, and more. Stream live matches online free without
any subscription or registration required. Our free sports streaming
platform provides HD quality live football streams, live soccer streams,
and live sports TV channels from around the world. Whether you want to
watch football online free or stream live sports in HD, EVaultHub
delivers the best free live streaming experience on any device."
Col 2: Quick Links: Home, Schedule, Privacy Policy, DMCA, Contact
Col 3: Telegram Join Card (styled as in section 5B)
Bottom bar: Copyright 2026 EVaultHub | Disclaimer text

Animations:
LIVE badge: pulsing red dot (@keyframes pulse)
Card hover: border-color #e94560, scale(1.02), transition 0.2s
Telegram button: hover glow blue
Share buttons: hover scale(1.08)

═══════════════════════════════════════════════
11. LARAVEL FILE STRUCTURE
    ═══════════════════════════════════════════════
    app/
    Http/
    Controllers/
    HomeController.php
    MatchController.php
    LeagueController.php
    ScheduleController.php
    SitemapController.php
    Api/MatchApiController.php
    Admin/AdminAuthController.php
    Admin/DashboardController.php
    Admin/ImportController.php
    Admin/MatchAdminController.php
    Admin/AnalyticsController.php
    Middleware/
    AdminAuth.php
    Models/
    Server.php
    Match.php         (use SoftDeletes)
    MatchStream.php
    MatchView.php
    AdminUser.php
    Services/
    MatchService.php  (fetch JSON, parse, fingerprint, cache 5min)
    ImportService.php (normalize + import logic)
    AnalyticsService.php

resources/views/
layouts/
app.blade.php       (public layout)
admin.blade.php     (admin layout with sidebar)
components/
match-card.blade.php
ad-leaderboard.blade.php
ad-responsive.blade.php
ad-small.blade.php
ad-mobile.blade.php
ad-bottom.blade.php
nav.blade.php
footer.blade.php
home/index.blade.php
match/show.blade.php
league/show.blade.php
schedule/index.blade.php
sitemap/index.blade.php
pages/
privacy.blade.php
dmca.blade.php
contact.blade.php
admin/
login.blade.php
dashboard.blade.php
import/index.blade.php
import/preview.blade.php
matches/index.blade.php
matches/create.blade.php
matches/edit.blade.php
analytics/index.blade.php

routes/
web.php
api.php

═══════════════════════════════════════════════
12. IMPORTANT TECHNICAL NOTES
    ═══════════════════════════════════════════════
1. JSON Fetch Caching:
   Cache::remember('server_1_json', 300, fn() => Http::get($url)->json())
   Use separate cache keys per server.

2. Slug Storage:
   Generate slug on import: Str::slug($title . ' ' . $date)
   Store in matches.slug column (unique). Look up match by slug in MatchController.

3. View Throttling:
   Before inserting match_view, check:
   SELECT COUNT(*) FROM match_views
   WHERE match_id=? AND ip_address=? AND viewed_at > NOW() - INTERVAL 1 HOUR
   If >= 1, skip insert.

4. HLS.js for m3u8:
   Load HLS.js from CDN only on match detail pages where stream_type='m3u8'
   <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
   Initialize:
   if(Hls.isSupported()) {
   var hls = new Hls();
   hls.loadSource('{m3u8_url}');
   hls.attachMedia(videoElement);
   } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
   video.src = '{m3u8_url}';
   }

5. .htaccess:
   Add Header always append X-Frame-Options SAMEORIGIN for admin.
   For public pages do NOT add X-Frame-Options (need to embed iframes).
   Content-Security-Policy: frame-src * (allow all iframe sources).

6. Admin Security:
   Hash passwords with bcrypt.
   Session-based auth only for /admin/* routes.
   CSRF on all forms.
   Rate limit login: 5 attempts per minute.

7. Performance:
   Eager load match_streams with matches (->with('streams'))
   Index: matches.server_id, matches.match_datetime, matches.deleted_at
   Paginate admin tables (20 per page), public listing (24 per page)

8. Mobile sticky ad:
   Add padding-bottom: 60px to <body> on mobile to prevent sticky ad
   covering content.

9. Chart.js on analytics page:
   Load from CDN: https://cdn.jsdelivr.net/npm/chart.js
   Pass data as JSON from controller to Blade, render bar chart.

10. No duplicate adsbygoogle.js script tags.
    Load it ONCE in <head>. Each ad unit just calls push({}).

═══════════════════════════════════════════════
13. DELIVERABLE CHECKLIST
    ═══════════════════════════════════════════════
    Do NOT skip any of these. Build every single one:
    ☐ All DB migrations (servers, matches, match_streams, match_views, admin_users)
    ☐ Database seeders (servers data, admin user)
    ☐ All Models with relationships and SoftDeletes
    ☐ MatchService with caching
    ☐ ImportService with fingerprint dedup
    ☐ All public controllers and routes
    ☐ All admin controllers and routes
    ☐ API endpoints (/api/matches, /api/match/{id})
    ☐ All Blade views (public + admin)
    ☐ All 5 AdSense Blade components
    ☐ Homepage with Server 1/2 tabs (NO merge)
    ☐ Match player with lazy load, channel tabs, HLS support
    ☐ Share buttons (WhatsApp, Telegram, Twitter/X, Facebook, Copy Link)
    ☐ Telegram join card
    ☐ How to Watch section
    ☐ Related Matches horizontal scroll
    ☐ Admin: Login page
    ☐ Admin: Dashboard with stats
    ☐ Admin: Import (server select → fetch → preview with checkboxes → import)
    ☐ Admin: Manage Matches (filter, paginate, edit, create, soft delete)
    ☐ Admin: Analytics (today/yesterday/7d, server breakdown, top 10, chart)
    ☐ SEO: all meta tags, canonical, OG, schema on every page
    ☐ Sitemap.xml
    ☐ robots.txt
    ☐ Privacy Policy page
    ☐ DMCA page
    ☐ Contact page
    ☐ Mobile responsive everything
    ☐ Mobile sticky ad (300x50)
    ☐ Dark theme throughout
    ☐ .env with correct DB credentials

Start with:
composer create-project laravel/laravel evaulthub
cd evaulthub

Then configure .env, create all migrations, run:
php artisan migrate --seed

Build every file completely. No placeholder comments. No "TODO" stubs.
Every function must be fully implemented and working.