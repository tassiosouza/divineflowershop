# Deploy to Kinsta

## Required GitHub secrets

In your repo: **Settings → Secrets and variables → Actions → New repository secret**. Add:

| Secret         | Value              | Example                    |
|----------------|--------------------|----------------------------|
| `SSH_HOST`     | Server host        | `35.236.52.109`            |
| `SSH_PORT`     | SSH port           | `64770`                    |
| `SSH_USER`     | SSH username       | `divineflowershop`         |
| `SSH_PASSWORD` | SSH password       | *(your password)*          |
| `DEPLOY_PATH`  | Remote site root   | `/www/divineflowershop_927/public` (this project) |

**Important:** Do not commit passwords. Use only GitHub Actions secrets for credentials.

## Behavior

- Runs on every **push to `master`**.
- Syncs the repo to the server with `rsync` (overwrites files, removes files that no longer exist in the repo).
- Excludes: `.git`, `wp-content/uploads`, `wp-config.php`, `.github` so uploads and server config are not overwritten.

## Finding `DEPLOY_PATH`

For this Kinsta site the deploy path is `/www/divineflowershop_927/public` (the folder that contains `wp-admin`, `wp-content`, `wp-includes`).
