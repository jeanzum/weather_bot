import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Generate or get persistent session UUID
function getOrCreateSessionUuid() {
  let sessionUuid = localStorage.getItem('chat_session_uuid');
  if (!sessionUuid) {
    sessionUuid = crypto.randomUUID();
    localStorage.setItem('chat_session_uuid', sessionUuid);
  }
  return sessionUuid;
}

// Add session UUID to all requests
window.axios.defaults.headers.common['X-Chat-Session-UUID'] = getOrCreateSessionUuid();
