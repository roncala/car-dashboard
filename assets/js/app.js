function csrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function baseUrl() {
  return window.APP_BASE_URL || '';
}

async function parseJsonStrict(res) {
  const ct = (res.headers.get('content-type') || '').toLowerCase();
  const text = await res.text();

  let data = null;
  if (ct.includes('application/json')) {
    try { data = JSON.parse(text); } catch { data = null; }
  }

  if (!res.ok) {
    const msg = (data && data.error) ? data.error : `Request failed (${res.status})`;
    throw new Error(msg + (data && data.detail ? ` — ${data.detail}` : ''));
  }

  if (!ct.includes('application/json')) {
    throw new Error(`Expected JSON but got "${ct || 'unknown'}". Response starts: ${text.slice(0, 140)}`);
  }

  return data || {};
}

async function apiPost(url, bodyObj) {
  const res = await fetch(baseUrl() + url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken()
    },
    body: JSON.stringify(bodyObj || {})
  });
  return parseJsonStrict(res);
}

async function apiGet(url) {
  const res = await fetch(baseUrl() + url, { credentials: 'same-origin' });
  return parseJsonStrict(res);
}

