#!/usr/bin/env bash
set -euo pipefail

QUEUE_CONNECTION="${QUEUE_CONNECTION:-database_tenant}"
QUEUE_NAMES="${QUEUE_NAMES:-notifications,import,export,activity-log,processing,low}"

php artisan queue:work "$QUEUE_CONNECTION" --queue="$QUEUE_NAMES" --sleep=3 --tries=3 --timeout=3600 "$@"
