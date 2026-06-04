# syntax=docker/dockerfile:1
#
# HKBP-specific Auténtico image: pinned upstream + the minimal admin-generated
# setup-password-link patch (validated by spike 001). Keep the fork limited to
# the setup-link endpoint; consider upstreaming later.
#
# Mirrors upstream's own build flow (`make fast` builds the admin/account UI
# dist assets — required because pkg/account/embed.go embeds them — then compiles
# the `autentico` binary from main.go). We inject the patch + route registration
# and run the focused tests before building.

FROM golang:1.25-bookworm AS build
WORKDIR /src

# Pin a specific upstream commit/tag. Override at build time:
#   docker build --build-arg AUTENTICO_REF=<commit-sha> -t hkbp-autentico idp/
ARG AUTENTICO_REPO=https://github.com/eugenioenko/autentico.git
ARG AUTENTICO_REF=main

# Node 22 + pnpm 10 are needed for the UI dist build.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git ca-certificates make curl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install -g pnpm@10 \
    && rm -rf /var/lib/apt/lists/*

RUN git clone "${AUTENTICO_REPO}" . \
    && git checkout "${AUTENTICO_REF}" \
    && git rev-parse HEAD > /UPSTREAM_COMMIT

# Apply the HKBP setup-password-link handler + tests, register the route, and run
# the focused tests (these packages do not need UI dist assets).
COPY patches/0001-admin-setup-password-link.patch /tmp/0001.patch
COPY scripts/register-route.sh /tmp/register-route.sh
RUN git apply --3way /tmp/0001.patch \
    && sh /tmp/register-route.sh . \
    && go test ./pkg/passwordreset ./pkg/user -run 'TestHKBPSpike|TestCreateUser' -count=1

# Build UI dist (fast, skips tsc) + the `autentico` binary. `make fast` runs
# admin-ui-build-fast, account-ui-build-fast, then `go build -o autentico main.go`.
RUN CI=true CGO_ENABLED=0 make fast

FROM debian:bookworm-slim AS runtime
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates sqlite3 \
    && rm -rf /var/lib/apt/lists/* \
    && useradd --system --create-home --home-dir /app appuser
WORKDIR /app/data
COPY --from=build /src/autentico /usr/local/bin/autentico
COPY --from=build /UPSTREAM_COMMIT /app/UPSTREAM_COMMIT
RUN chown -R appuser:appuser /app
USER appuser
# Auténtico serves OIDC under /oauth2 and listens on 9999 by default; SQLite
# state lives at AUTENTICO_DB_FILE_PATH on the mounted volume.
ENV AUTENTICO_DB_FILE_PATH=/app/data/autentico.db
EXPOSE 9999
ENTRYPOINT ["autentico"]
CMD ["start"]
