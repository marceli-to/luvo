import http from '@/lib/http';
import tinyConfig from '@/config/tiny.js';

/**
 * TinyMCE config with the uploaded files as link list. A fresh object per
 * call; editors don't share (and mutate) one config.
 */
export function useTinyConfig() {
  return {
    ...tinyConfig,
    link_list: success => http.get('/api/files').then(response => success(response.data)),
  };
}
