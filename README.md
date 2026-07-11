![AuraNexus Banner](https://jpcdn.it/img/b54ba3aff92ea2732ce9462934ddad15.png)

# AuraNexus

![Status](https://img.shields.io/badge/Status-Live-brightgreen)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-4479A1)
![License](https://img.shields.io/badge/License-MIT-blue)

AuraNexus is a full-stack community and content platform built on Laravel. It combines a public-facing forum system, where members post, discuss, and share content across categories and tags, with a complete administrative back office for moderation, permissions, monetization, and site configuration. It was built as an end-to-end demonstration of production-oriented Laravel development: real permission enforcement, sanitized user-generated content, a self-hosted image pipeline, SEO-correct output, and a UI layer built from scratch rather than a starter kit.

Live: [auranexus.ydulaj.com](https://auranexus.ydulaj.com/)
Case study: [ydulaj.com/projects/Aura-Nexus](https://ydulaj.com/projects/Aura-Nexus)

## Overview

The platform is organized around forums grouped into categories, with tags used for cross-cutting discovery independent of category structure. Members create posts through a rich text editor, react to and comment on them, save posts to a personal list, and build a public profile. Everything a member can do is governed by an explicit role and permission system rather than hardcoded checks scattered through the codebase, so granting or revoking a capability (posting, moderating, accessing the admin panel) is a data change, not a code change.

On the administrative side, AuraNexus ships with a full back office: user management with role assignment and per-user permission overrides, category and forum customization, paragraph templates for guided posting, homepage tag-card curation, an ad-placement manager for monetization, theme customization backed by CSS custom properties, site-wide settings (SEO defaults, registration controls, minimum age, social links), and moderation queues for reported content and removed posts.

## Core Features

### Content and community

- Categories, forums, and tags as three independent, cross-linked ways to organize and discover content
- A rich post editor with a grouped icon toolbar (bold, italic, headings, lists, alignment, image and social-link insertion), backed by real HTML sanitization on every save
- Legacy BBCode-style image tags (`[url=..][img]..[/img][/url]`) are automatically converted to real `<img>` elements on save and on display, so content pasted from older sources still renders correctly
- Comments with a moderation queue for pending approval, reactions, saving posts for later, and one-click sharing
- Recent and related post recommendations on every post page, the latter ranked by tag overlap rather than plain recency
- A link-unlock system for gating outbound download links behind a short wait, with server-generated short codes rather than exposing the original URL directly

### Accounts, roles, and permissions

- Registration with email verification, disposable-email blocking, Google reCAPTCHA, and an admin-configurable minimum age enforced against date of birth
- A role and permission model (`Role`, `Permission`, per-user permission overrides) checked through a single `hasPermission()` call on the user, rather than ad hoc role string comparisons
- Account status handling for suspensions and bans, including automatic expiry of suspensions on the next request
- Public user profiles showing a member's post history and activity

### Administration

- A dashboard summarizing platform activity, page views, and search trends
- User management: search, role assignment, permission overrides, avatar handling, and suspension/ban controls, with safeguards against removing the last remaining admin
- Category, forum, and paragraph-template customization from the admin panel
- An ad-placement manager for inserting ad code at fixed positions across the site
- Theme customization backed by CSS custom properties, so a color change in the admin panel propagates through the entire front end without editing templates
- Site settings covering SEO defaults, registration availability, minimum age, footer links, and social profiles
- Moderation queues for reported posts/comments and a log of removed content with the reason and moderator attached

### Discovery and SEO

- A dynamically generated sitemap and `robots.txt`, both built from the application's own configured domain rather than hardcoded values, and filtered to published content only
- Structured data (JSON-LD) rendered for posts and the homepage
- Server-rendered canonical URLs, Open Graph and Twitter card metadata, and per-page meta descriptions and titles, all overridable per view and falling back to admin-configured defaults
- Trending and top-article listings computed from view counts and pin state rather than static curation

### Media and performance

- A self-hosted, on-demand image thumbnailing service: the first request for a given image size fetches the source once, resizes and re-encodes it to WebP, and caches the result to disk; every subsequent request is served from that cache
- Thumbnail generation is guarded against server-side request forgery (private and loopback IP ranges are rejected, only `http`/`https` sources are allowed, and both response size and content type are validated before decoding)
- Thumbnail generation happens per image behind a signed route rather than during page rendering, so a slow or unreachable source image never blocks the rest of the page from loading
- Native lazy loading on below-the-fold images, with the single above-the-fold hero image prioritized instead
- Database indexes on the columns actually used by moderation and analytics queries, and cached lookups for settings and permissions that would otherwise be re-queried on every request

## Architecture Notes

Authorization is centralized on the `User` model through `hasRole()` and `hasPermission()`, backed by `Role`, `Permission`, and a `permission_user` pivot for per-user overrides. Controllers call these methods directly rather than duplicating authorization logic, and route middleware (`AdminMiddleware`, `EnsureUserHasPermission`, `AccountStatusMiddleware`) enforces the same rules at the routing layer as a second line of defense.

User-submitted HTML (post content, static pages) is passed through `mews/purifier` with an explicit allowed-tag list before it is stored, and the same sanitization runs again on display for defense in depth. Ad placement HTML is treated differently: it is restricted to true administrators rather than sanitized, since ad network embeds legitimately require `<script>` and `<iframe>` tags that would otherwise be stripped.

The image pipeline is implemented as a dedicated `ThumbnailService` behind a signed `ThumbnailController` route, rather than resizing images inline during a page request. This keeps page response times independent of how large or slow a linked image happens to be, and avoids depending on a third-party image proxy.

The front end is Blade and Tailwind CSS, compiled through Vite, with no client-side framework. Interactive pieces (the post editor, tag input, sidebar navigation, theme toggling) are implemented as small, self-contained vanilla JavaScript modules rather than a single-page application, matching the server-rendered, multi-page nature of the rest of the site.

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Database | MySQL / MariaDB |
| Frontend | Blade templates, Tailwind CSS 4, Vite |
| Sanitization | HTMLPurifier via mews/purifier |
| SEO | spatie/laravel-sitemap |
| Spam protection | Google reCAPTCHA via anhskohbo/no-captcha |
| Sessions, cache, queue | Database-backed drivers |

## Local Setup

The application follows a standard Laravel installation.

```bash
git clone https://github.com/Dulaj007/AuraNexus.git
cd AuraNexus
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure a MySQL database in `.env`, then run migrations:

```bash
php artisan migrate
```

Build front-end assets and start the development server:

```bash
npm run build
php artisan serve
```

For active front-end development, `npm run dev` starts the Vite dev server with hot module replacement.

## Project Structure

The codebase follows Laravel's standard layout, with a few points worth knowing:

- `app/Http/Controllers` is split into `Admin`, `Public`, and `User` namespaces, separating administrative actions, unauthenticated public pages, and actions that require a logged-in member
- `app/Services` holds logic that does not belong on a model or in a controller: image thumbnailing, BBCode conversion, outbound link gating, and theme CSS generation
- `resources/views/layouts` has a dedicated layout per page type (post, forum, category, search, profile, auth, posting) rather than one universal layout, since each type needs different metadata and structure
- `resources/views/partials` holds the shared chrome (navigation, sidebar, footer, background effects, splash screen) included across layouts

## What This Project Demonstrates

AuraNexus was built to show the full surface of a production Laravel application rather than an isolated feature: schema and migration design, an authorization model that scales past a handful of hardcoded checks, sanitization of user-generated content, SEO output that is actually correct rather than superficially present, a self-hosted media pipeline with real security guards, and an admin back office with the moderation and configuration tools a live community site actually needs.
