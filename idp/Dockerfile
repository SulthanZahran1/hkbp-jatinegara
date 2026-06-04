# syntax=docker/dockerfile:1
#
# HKBP-specific Auténtico image: pinned upstream + the minimal admin-generated
# setup-password-link patch (validated by spike 001). Keep the fork limited to
# the setup-link endpoint; consider upstreaming later.

FROM golang:1.25-bookworm AS build
WORKDIR /src

# Pin a specific upstream commit/tag. Override at build time:
#   docker build --build-arg AUTENTICO_REF=<commit-sha> -t hkbp-autentico idp/
ARG AUTENTICO_REPO=https://github.com/eugenioenko/autentico.git
ARG AUTENTICO_REF=main

RUN apt-get update \
    && apt-get install -y --no-install-recommends git ca-certificates make nodejs npm \
    && rm -rf /var/lib/apt/lists/*

RUN git clone "${AUTENTICO_REPO}" . \
    && git checkout "${AUTENTICO_REF}" \
    && git rev-parse HEAD > /UPSTREAM_COMMIT

# Apply the HKBP setup-password-link handler + tests, then register the route.
COPY patches/0001-admin-setup-password-link.patch /tmp/0001.patch
COPY scripts/register-route.sh /tmp/register-route.sh
RUN git apply --3way /tmp/0001.patch \
    && sh /tmp/register-route.sh . \
    && go test ./pkg/passwordreset ./pkg/user -run 'TestHKBPSpike|TestCreateUser' -count=1

# Build the account/admin UI dist assets (required before e2e compiles and for
# the embedded /oauth2/reset-password page the setup link points at).
RUN make admin-ui-build-fast account-ui-build-fast || true

RUN CGO_ENABLED=0 GOOS=linux go build -o /out/autentico ./... 2>/dev/null \
    || CGO_ENABLED=0 GOOS=linux go build -o /out/autentico .

FROM debian:bookworm-slim AS runtime
WORKDIR /app
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && useradd --system --create-home --home-dir /app appuser
COPY --from=build /out/autentico /app/autentico
COPY --from=build /UPSTREAM_COMMIT /app/UPSTREAM_COMMIT
RUN mkdir -p /app/data && chown -R appuser:appuser /app
USER appuser
# Auténtico stores SQLite state at AUTENTICO_DB_FILE_PATH; mount /app/data.
ENV AUTENTICO_DB_FILE_PATH=/app/data/autentico.db
EXPOSE 8080
CMD ["/app/autentico"]
