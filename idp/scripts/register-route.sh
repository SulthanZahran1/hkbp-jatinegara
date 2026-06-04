#!/usr/bin/env sh
# Registers the setup-password-link admin route in the Auténtico source tree.
#
# The handler itself is added by patches/0001-admin-setup-password-link.patch.
# This script wires the route, which is the only step that depends on upstream's
# current router layout (see patches/0002-register-setup-link-route.md).
#
# It searches for the existing `POST /admin/api/users` registration and inserts
# the setup-link registration immediately after it. If the anchor is not found,
# the script exits non-zero so the image build fails loudly rather than shipping
# without the route.
set -eu

SRC_DIR="${1:-.}"
ROUTE_LINE='	mux.HandleFunc("POST /admin/api/users/{id}/setup-password-link", passwordreset.HandleAdminCreateSetupPasswordLink)'

# Find the file that registers POST /admin/api/users (method+path mux pattern).
target="$(grep -rl 'POST /admin/api/users"' "$SRC_DIR" --include='*.go' | head -n1 || true)"
if [ -z "$target" ]; then
  echo "register-route: could not find the 'POST /admin/api/users' anchor under $SRC_DIR." >&2
  echo "register-route: upstream router layout changed; update idp/patches/0002-register-setup-link-route.md and this script for the pinned AUTENTICO_REF." >&2
  exit 1
fi

if grep -q 'setup-password-link' "$target"; then
  echo "register-route: route already present in $target; nothing to do."
  exit 0
fi

# Insert the new registration on the line after the anchor.
awk -v line="$ROUTE_LINE" '
  { print }
  /POST \/admin\/api\/users"/ && !done { print line; done=1 }
' "$target" > "$target.tmp" && mv "$target.tmp" "$target"

# Ensure the passwordreset package is imported in the target file.
if ! grep -q 'pkg/passwordreset' "$target"; then
  echo "register-route: NOTE — add the passwordreset import to $target if the build complains." >&2
fi

echo "register-route: registered setup-password-link route in $target"
