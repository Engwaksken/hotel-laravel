# HMS Phase 13 API additions

All endpoints below require a valid Laravel Sanctum token.

## Staff operations
- `GET /api/v1/staff/tasks` — outstanding housekeeping, maintenance and guest-service tasks assigned to the linked employee.
- `GET /api/v1/staff/kitchen` — current kitchen tickets for the employee's property.

## Owner / management mobile API
- `GET /api/v1/management/dashboard` — 30-day property KPI summary. Requires `management.view`.
- `GET /api/v1/management/snapshots` — recent daily KPI snapshots. Requires `management.view`.
- `GET /api/v1/management/alerts` — warning/critical security events. Requires `security.audit`.

Property scoping is derived from the authenticated user. The client does not supply an arbitrary property id for normal property users.
