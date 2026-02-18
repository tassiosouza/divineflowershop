# Release

This workflow will trigger a release/deployment when a milestone is closed. It won;t create a changelog at this point, but it will update versions and release dates.

## Requirements

* `SLACK_WEBHOOK` secret
* `SSH_PRIVATE_KEY` secret
* `PLUGIN_NAME` secret
* `PLUGIN_URL` secret
