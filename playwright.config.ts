import { fileURLToPath } from 'node:url';
import { defineConfig, devices } from '@playwright/test';

const port = 8010;
const baseURL = `http://127.0.0.1:${port}`;

/**
 * The SQLite file of the e2e server, recreated on every run and gitignored
 * by database/.gitignore.
 */
const databasePath = fileURLToPath(
    new URL('./database/e2e.sqlite', import.meta.url),
);

export default defineConfig({
    testDir: 'tests/e2e',
    forbidOnly: !!process.env.CI,
    // The database is fresh once per run, not per test: a retry would see the
    // data of the failed attempt, so failures are not retried.
    retries: 0,
    workers: 1,
    reporter: 'list',
    use: {
        baseURL,
        trace: 'retain-on-failure',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        command: [
            `rm -f "${databasePath}"`,
            `touch "${databasePath}"`,
            'php artisan migrate:fresh --force',
            // Without --no-reload, serve drops the variables below from the
            // server process, and it would fall back to the default database.
            `php artisan serve --no-reload --host=127.0.0.1 --port=${port}`,
        ].join(' && '),
        url: baseURL,
        reuseExistingServer: false,
        timeout: 60_000,
        env: {
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: databasePath,
            APP_URL: baseURL,
        },
    },
});
