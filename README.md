<div align="center">
  <img src="https://upload.wikimedia.org/wikipedia/en/thumb/8/8b/YuppTV_logo.svg/960px-YuppTV_logo.svg.png" alt="YuppTV Logo" width="200"/>

  # YuppTV M3u8 Extractor

  A lightweight PHP API script to fetch and redirect to live YuppTV HLS (`.m3u8`) streams.

  ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
</div>

---

## Overview

This script acts as middleware to extract direct `.m3u8` streaming URLs from YuppTV's backend APIs. It handles session token generation automatically, caches the token locally to minimize API calls, and redirects compatible media players to the raw stream.

---

## Features

- **Automated Session Management** — Fetches and caches the required `sessionId` in a local JSON file.
- **Clean API Requests** — Uses only the essential headers required by the Revlet API.
- **Auto-Redirect** — Instantly redirects GET requests to the parsed `.m3u8` stream.
- **Token Refresh Endpoint** — Dedicated trigger to rotate or refresh session tokens on demand.

---

## Prerequisites

- Apache or Nginx web server (or a Linux VPS)
- PHP with the **cURL** extension enabled (`php-curl`)

---

## Installation

**1. Clone the repository:**

```bash
git clone https://github.com/yourusername/yupptv-m3u8-extractor.git
cd yupptv-m3u8-extractor
```

**2. Deploy to your server:**

Place `index.php` in your web root (e.g., `/var/www/html/yupptv/`). Ensure the PHP process has **write permissions** on that directory so it can create and update `session_id.json`.

---

## Usage

Pass the channel path via the `id` query parameter:

```
https://api.example.com/yupptv/index.php?id=CHANNEL_PATH_HERE
```

The script will generate a session token if one doesn't exist, then redirect to the live `.m3u8` stream.

---

## Automating Token Refreshes

Session tokens expire over time. Use a cron job to refresh them automatically.

**Open crontab:**

```bash
crontab -e
```

**Add a rule to refresh every 12 hours:**

```bash
0 */12 * * * curl -s "http://127.0.0.1/yupptv/index.php?id=refresh_token" > /dev/null
```

---

## Directory Structure

```
yupptv-m3u8-extractor/
├── index.php          # Main extractor script
└── session_id.json    # Auto-generated token cache (created at runtime)
```
