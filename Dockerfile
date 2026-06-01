# syntax=docker/dockerfile:1

FROM node:22-alpine AS frontend-build
WORKDIR /src/frontend
RUN corepack enable
COPY frontend/package.json frontend/pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile
COPY frontend/ ./
ARG VITE_API_BASE_URL=/api/v1
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}
RUN pnpm build

FROM golang:1.25-bookworm AS backend-build
WORKDIR /src/backend
COPY backend/go.mod backend/go.sum ./
RUN go mod download
COPY backend/ ./
RUN CGO_ENABLED=1 GOOS=linux go build -o /out/hkbp-server ./cmd/server

FROM debian:bookworm-slim AS runtime
WORKDIR /app
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && useradd --system --create-home --home-dir /app appuser
COPY --from=backend-build /out/hkbp-server /app/hkbp-server
COPY backend/migrations /app/migrations
COPY --from=frontend-build /src/frontend/dist /app/frontend/dist
RUN mkdir -p /app/uploads /app/data \
    && chown -R appuser:appuser /app
ENV PORT=8080 \
    STATIC_DIR=/app/frontend/dist \
    UPLOAD_DIR=/app/uploads \
    MAX_UPLOAD_SIZE=5242880
EXPOSE 8080
CMD ["/app/hkbp-server"]
