import axios from 'axios';

// Same-origin in production (the Go server serves the built SPA), proxied to the
// backend in dev. Auth is the HTTP-only hkbp_session cookie, so every request
// must send credentials — there are no Bearer tokens anymore.
const baseURL = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';

const client = axios.create({
  baseURL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json'
  }
});

client.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;
    const url: string = error.config?.url ?? '';
    // A 401 on a normal API call means the cookie session expired or was revoked;
    // bounce to login. The /auth/me probe is exempt so the router guard can treat
    // its 401 as "not signed in" without causing a redirect loop.
    if (status === 401 && !url.includes('/auth/me') && window.location.pathname !== '/login') {
      window.location.assign('/login');
    }
    return Promise.reject(error);
  }
);

export default client;
