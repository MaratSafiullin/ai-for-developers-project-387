# Project Workflow

- Set up a fresh clone with `composer setup`: it installs PHP and npm dependencies, enables the git hooks in `.githooks`, creates `.env`, generates the app key, migrates the SQLite database, and builds the frontend.
- Start the app for development with `composer dev`. The full command list for humans is in `README.md`.
- Run `composer ci:check` as the final gate before declaring work done. It chains the Vite+ lint and format check, the contract check, `tsc`, Pint, Larastan, and Pest, and CI runs the same command.
- The API contract is TypeSpec in `contract/main.tsp`. After editing it, run `npm run contract:generate` and commit the regenerated `contract/openapi.yaml` and `resources/js/api/` SDK; never edit them by hand. `npm run contract:check` fails on uncommitted or unstaged drift in those paths.
- While working, run the narrow checks: `php artisan test --compact <path>` for tests, `vendor/bin/pint --dirty --format agent` for PHP style, `npm run check:fix` for TS/React lint and format.
- Frontend tooling is Vite+ (`vp`) configured in `vite.config.ts`, not plain Vite.
- Specs and tickets for the current work live in GitHub Issues. See `docs/agents/issue-tracker.md`.
