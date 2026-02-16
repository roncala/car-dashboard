function csrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function baseUrl() {
  return window.APP_BASE_URL || '';
}
 
function escapeHtml(s) {
         return String(s ?? '').replace(/[&<>"']/g, (m) => ({
             '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
               }[m]));

               }
async function apiGet(url) {
                 const res = await fetch(baseUrl() + url, { credentials: 'same-origin' });
                   const data = await res.json().catch(() => ({}));
                     if (!res.ok) throw new Error(data.error || `Request failed (${res.status})`);
                       return data;
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
                                                             const data = await res.json().catch(() => ({}));
                                                               if (!res.ok) throw new Error(data.error || `Request failed (${res.status})`);
                                                                 return data;
                                                                 }
  
