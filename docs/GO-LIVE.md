# Phase 14 Go-live Runbook

1. Put the application behind HTTPS and set `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_SECURE_COOKIE=true`.
2. Run migrations and `HotelGoLiveSeeder`.
3. Complete `/admin/setup`: property identity, users, rooms/rates, taxes, gateways, printers, messaging, backups.
4. Keep all payment gateways in sandbox until provider callback/status verification passes end-to-end.
5. Verify Uganda statutory tax configuration with the hotel's accountant before enabling a tax rule; the seeder intentionally creates VAT disabled at 0 rather than hard-coding a legal rate.
6. Run room/rate CSV imports on staging first. Review `data_import_runs` failures.
7. Configure queue workers and scheduler. Run `php artisan hotel:health-check --store`.
8. Create and verify a full backup. Run a restore **dry-run**. The current live restore worker is database-only; file/full live restores are blocked until file payload restoration is implemented and tested. Perform any database live restore only in a maintenance window against a verified recovery plan.
9. Complete Front Desk → POS room charge → payment → checkout → housekeeping E2E test.
10. Complete procurement → GRN → stock → supplier invoice/payment → ledger test.
11. Run Night Audit on a staging business date and verify the next business date opens correctly.
12. Validate manager/property isolation with at least two test properties before multi-property production.
