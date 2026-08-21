# Copilot Instructions For InscripcionesU

## Stack and style

- Laravel 13 + PHP 8.3.
- Keep controllers readable and avoid mixing unrelated concerns.
- Prefer validation via FormRequest or explicit `$request->validate([...])` before writes.
- Keep Blade views simple, with reusable partials when complexity grows.

## Domain rules for this project

- Enrollment financial logic is split by concept: inscription and tuition.
- Refund values must be represented per concept and net balances should remain coherent.
- Financial reports are filtered by movement date fields.

## Change policy

- Avoid destructive schema or data changes unless explicitly requested.
- Preserve route names and existing report filters.
- Keep exports backward compatible unless asked to alter format.

## Verification

- For PHP changes, run syntax check on touched files and refresh Blade cache when relevant.
- Prefer targeted feature tests related to enrollment/reporting when available.

## Copilot behavior preference

- Propose minimal diffs first.
- Explain risks when touching financial calculations or exports.
- When uncertain, ask for confirmation before broad refactors.
