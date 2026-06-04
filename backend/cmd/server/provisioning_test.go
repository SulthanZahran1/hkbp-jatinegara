package main

import (
	"context"
	"net/http"
	"net/http/httptest"
	"sync/atomic"
	"testing"
)

// TestIdPClientAdminToken verifies the idpClient mints an admin token via the
// password grant, attaches it as a Bearer to admin API calls, and caches it
// across calls.
func TestIdPClientAdminToken(t *testing.T) {
	var tokenCalls, adminCalls int32
	mux := http.NewServeMux()
	mux.HandleFunc("/token", func(w http.ResponseWriter, r *http.Request) {
		atomic.AddInt32(&tokenCalls, 1)
		_ = r.ParseForm()
		if r.Form.Get("grant_type") != "password" || r.Form.Get("client_id") != "autentico-admin" ||
			r.Form.Get("username") != "hkbp-admin" || r.Form.Get("password") != "pw" {
			http.Error(w, "bad grant", http.StatusBadRequest)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		_, _ = w.Write([]byte(`{"access_token":"adm-tok","expires_in":900,"token_type":"Bearer"}`))
	})
	mux.HandleFunc("/admin/api/users", func(w http.ResponseWriter, r *http.Request) {
		atomic.AddInt32(&adminCalls, 1)
		if r.Header.Get("Authorization") != "Bearer adm-tok" {
			http.Error(w, "unauthorized", http.StatusUnauthorized)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		_, _ = w.Write([]byte(`{"data":{"id":"sub-1"}}`))
	})
	srv := httptest.NewServer(mux)
	defer srv.Close()

	c := newIDPClient(Config{
		IdPAdminBaseURL:  srv.URL,
		IdPTokenURL:      srv.URL + "/token",
		IdPAdminClientID: "autentico-admin",
		IdPAdminUsername: "hkbp-admin",
		IdPAdminPassword: "pw",
	})
	if !c.configured() {
		t.Fatal("client with admin creds should be configured")
	}

	for i := 0; i < 2; i++ {
		sub, err := c.CreateUser(context.Background(), "u", "bootstrap", "")
		if err != nil {
			t.Fatalf("CreateUser: %v", err)
		}
		if sub != "sub-1" {
			t.Fatalf("expected subject sub-1, got %s", sub)
		}
	}
	if got := atomic.LoadInt32(&tokenCalls); got != 1 {
		t.Fatalf("expected token minted once (cached), got %d", got)
	}
	if got := atomic.LoadInt32(&adminCalls); got != 2 {
		t.Fatalf("expected 2 admin calls, got %d", got)
	}
}

func TestIdPClientStaticTokenPrecedence(t *testing.T) {
	var tokenCalls int32
	mux := http.NewServeMux()
	mux.HandleFunc("/token", func(w http.ResponseWriter, r *http.Request) { atomic.AddInt32(&tokenCalls, 1) })
	var gotAuth string
	mux.HandleFunc("/admin/api/users", func(w http.ResponseWriter, r *http.Request) {
		gotAuth = r.Header.Get("Authorization")
		w.Header().Set("Content-Type", "application/json")
		_, _ = w.Write([]byte(`{"id":"x"}`))
	})
	srv := httptest.NewServer(mux)
	defer srv.Close()

	c := newIDPClient(Config{IdPAdminBaseURL: srv.URL, IdPTokenURL: srv.URL + "/token", IdPAdminToken: "static-tok"})
	if _, err := c.CreateUser(context.Background(), "u", "p", ""); err != nil {
		t.Fatalf("CreateUser: %v", err)
	}
	if gotAuth != "Bearer static-tok" {
		t.Fatalf("expected static token bearer, got %q", gotAuth)
	}
	if atomic.LoadInt32(&tokenCalls) != 0 {
		t.Fatal("static token must not call the token endpoint")
	}
}

func TestIdPClientNotConfigured(t *testing.T) {
	c := newIDPClient(Config{}) // no base URL, no creds
	if c.configured() {
		t.Fatal("client with no base URL must not be configured")
	}
}
