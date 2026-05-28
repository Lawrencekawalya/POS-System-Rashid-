# Project Instructions - Client 2

## Testing Workflow
For every new feature or significant modification added to this system, the following testing lifecycle MUST be followed:

1.  **Focused Testing:** Create or update specific tests for the feature. Run these tests in isolation to verify the core logic and edge cases.
    - Example: `php artisan test tests/Feature/NewFeatureTest.php`
2.  **End-to-End (E2E) Validation:** After focused tests pass, run the full test suite to ensure no regressions were introduced across the application.
    - Command: `php artisan test`

## Environment Configuration
- **Testing Environment:** Always use the `.env.testing` file for running tests. This ensures isolation from the local development database and consistent results across different environments.
- **CSRF in Tests:** CSRF protection is globally disabled in the `testing` environment via `tests/Pest.php` to facilitate feature testing of POST/PUT/PATCH/DELETE routes.

## Commit Conventions
- Use **Conventional Commits** (e.g., `feat:`, `fix:`, `refactor:`, `chore:`).
- Keep commit messages concise but descriptive of the "why" behind the change.
