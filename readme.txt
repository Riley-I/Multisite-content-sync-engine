# Multisite Content Sync Engine
A WordPress plugin designed for enterprise-scale multisite environments — enabling controlled content replication across a network, with queued sync jobs, ACF + media mapping, optional scheduled automation, and extensible APIs.

> Built as a case-study project to demonstrate senior-level architecture for WordPress plugin development, multisite infrastructure, and scalable engineering patterns.

---

## Problem This Solves
For organizations running *multiple sites under one brand* — e.g. dental chains, franchises, SaaS marketing microsites, regional businesses — maintaining content consistency is painful:

- Updating services, pricing, FAQs, policies, global blocks manually on 20–50 sites
- Copy-pasting pages across environments (high-risk, error-prone)
- No controlled way to push updates or track what changed

**Multisite Content Sync Engine** introduces a production-ready model commonly found in SaaS platforms — **source-of-truth → controlled content propagation**, with logging, rollback potential, and automation hooks.

---

## 🎯 Core Features (MVP → v1.0 Vision)

### ✔ MVP (shipped)
- Multisite-only plugin bootstrap
- Network admin UI (Dashboard, Sync Now, Settings)
- Namespaced OOP structure (`RID\MultisiteContentSync`)
- Service Provider architecture (Admin, API, Cron extendable)
- View template separation (clean, unit-testable)
- Admin asset bundling & namespaced JS/CSS
- SSH + Composer-ready repo, clean Git versioning

### 🚀 In-Progress
- Manual trigger sync: select **source → target** sites
- Post & Page sync (basic)
- Multi-select UI for network sites
- Basic settings registry (default source site, ACF toggle, logging retention)

### v1.0 Roadmap
| Feature | Description |
|---------|-------------|
| Queue + Job System | Async processing to avoid timeouts; DB-backed queue + WP-Cron runner |
| Schema Migrations | Create `mcs_jobs` + `mcs_logs` tables with version tracking |
| ACF Sync | Sync meta + ACF fields per post using integration layer |
| Media Mapping | Copy/attach media assets + rewrite URLs between sites |
| REST API Endpoints | `/wp-json/mcs/v1/sync` for remote triggering / CI workflows |
| WP-CLI Command | `wp mcs sync` to enable DevOps / CI automation |
| Versioning + Rollback | Store deltas + restore previous content state |
| Selective Sync Rules | Include/exclude post types, taxonomies, field groups |
| Permissions | Custom capabilities: `manage_multisite_sync`, `mcs_execute_job` |

---

## Architecture Overview
This plugin intentionally showcases modern engineering patterns for WordPress:

- **Namespaces + PSR-4 structure** (`src/…`)
- **Service Provider Pattern** (`AdminServiceProvider`, `ApiServiceProvider`, `CronServiceProvider`)
- **Single Responsibility Classes**
- **Separation of Concerns** — logic is NOT embedded in hooks
- **View Templates** live in `views/admin/…`
- **Future-proof** for Composer autoload + DI container
- **Scalable Sync Pipeline (planned)**  
  `SyncService → JobFactory → Queue → JobRunner → Handlers (Posts, Terms, Media, Options)`

```text
src/
├─ Plugin.php
├─ Admin/
│  └─ AdminServiceProvider.php
├─ API/
├─ Cron/
├─ Services/
├─ Sync/
├─ Database/
└─ Views/…
