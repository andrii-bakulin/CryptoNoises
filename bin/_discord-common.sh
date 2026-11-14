#!/usr/bin/env bash
# Shared helpers for bin/discord-* scripts. Not meant to be run directly.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
RUN="$ROOT/commands/run"
THREADS=(main-loop notifier assistant)

discord_parse_scope() {
    local scope="${1:-all}"

    case "$scope" in
        all|main-loop|notifier|assistant) echo "$scope" ;;
        *) echo "Usage: $(basename "$0") [all|main-loop|notifier|assistant]" >&2; exit 1 ;;
    esac
}

discord_show_threads() {
    ps ax | grep "$RUN" | grep "start/" | grep -v grep || true
}

discord_start() {
    local scope="$1"
    local threads=("$scope")

    [[ "$scope" == "all" ]] && threads=("${THREADS[@]}")

    for thread in "${threads[@]}"; do
        nohup "$RUN" "/discord/bot/start/$thread" silent=1 >/dev/null 2>&1 &
    done
}

discord_stop() {
    "$RUN" "/discord/bot/stop/$1"
}
