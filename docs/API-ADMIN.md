# Administration scope
Phase 14 deliberately keeps security-sensitive configuration on authenticated web administration routes rather than exposing credential CRUD through the public/mobile API. Secrets in payment and messaging config use Laravel encrypted casts. Blank secret inputs preserve the prior encrypted value.
