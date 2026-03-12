# Tournament Module for Ilch 2.0

Tournament management module with single-elimination bracket support.

## Features

- Tournament CRUD in admin
- Team management (captain, members, logo)
- Tournament registration and optional check-in
- Bracket generation (single elimination)
- Match scheduling and status handling
- Result reporting with evidence uploads/links
- Dispute workflow with admin resolution
- Frontend boxes:
  - Upcoming matches
  - Running tournaments

## Requirements

- Ilch Core `>= 2.2.0`
- PHP `>= 7.3`

## Installation

1. Copy this folder to:
   `application/modules/tournament`
2. Install the module in Ilch admin.
3. Ensure write permissions for:
   `application/modules/tournament/storage`

The module creates its database tables automatically via `config/config.php` during installation.

## Permissions (ACL)

- `tournament_admin`
- `tournament_manage`
- `tournament_dispute`
- `tournament_team_manage`
- `tournament_report`

## Notes

- Uploads/evidence are stored under `storage/`.
- This repository intentionally ignores runtime files in `storage/`.

## Release Workflow

You can automate tag + push + GitHub release with:

```powershell
cd application/modules/tournament
.\scripts\release.ps1 -Version 1.0.2
```

Authentication for GitHub Release creation:

- Option A: `gh auth login`
- Option B: set env var `GH_TOKEN`

Optional parameters:

```powershell
.\scripts\release.ps1 -Version 1.0.2 -Title "v1.0.2" -Notes "Bugfix release"
```
