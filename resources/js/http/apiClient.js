/**
 * Shared axios instance + helpers for migrating off $.easyAjax.
 * Loaded from resources/js/main.js — available as window.apiHttp on authenticated pages.
 *
 * Laravel: JSON replies use App\Helper\Reply (status success|fail); validation may be 422 with { errors }.
 * get/post/put/patch/postForm/postUrlEncoded/delete return the decoded JSON body (unwrapData), not axios
 * response — use `payload.status` / `payload.data`, never `payload.data.status` for the top-level Reply flag.
 */
const axios = require('axios');

function csrfToken() {
    if (typeof document === 'undefined') {
        return '';
    }
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
}

const apiClient = axios.create({
    baseURL: '',
    timeout: 120000,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
    withCredentials: true,
});

apiClient.defaults.xsrfCookieName = 'XSRF-TOKEN';
apiClient.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

apiClient.interceptors.request.use((config) => {
    const token = csrfToken();
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

/**
 * Normalize axios / Laravel errors for callers.
 */
function normalizeApiError(error) {
    if (error.isNormalized) {
        return error;
    }
    const res = error.response;
    if (!res) {
        const err = new Error(
            error.code === 'ECONNABORTED'
                ? 'Connection timed out. Please try again.'
                : error.message || 'Network error'
        );
        err.isNormalized = true;
        err.status = 0;
        err.errors = null;
        err.payload = null;
        return err;
    }
    const data = res.data;
    const payload = typeof data === 'object' && data !== null ? data : {};
    const message =
        payload.message ||
        (typeof data === 'string' ? data : '') ||
        (res.status === 422 ? 'Validation failed' : 'Request failed');
    const errors = payload.errors || null;
    const err = new Error(message);
    err.isNormalized = true;
    err.status = res.status;
    err.errors = errors;
    err.payload = payload;
    return err;
}

apiClient.interceptors.response.use(
    (response) => {
        const d = response.data;
        if (d && typeof d === 'object' && d.status === 'fail') {
            const err = new Error(d.message || 'Request failed');
            err.isNormalized = true;
            err.status = response.status;
            err.errors = d.errors || null;
            err.payload = d;
            return Promise.reject(err);
        }
        return response;
    },
    (error) => Promise.reject(normalizeApiError(error))
);

function unwrapData(promise) {
    return promise.then((response) => response.data);
}

function get(url, config) {
    return unwrapData(apiClient.get(url, config));
}

function post(url, data, config) {
    return unwrapData(apiClient.post(url, data, config));
}

function put(url, data, config) {
    return unwrapData(apiClient.put(url, data, config));
}

function patch(url, data, config) {
    return unwrapData(apiClient.patch(url, data, config));
}

/**
 * Laravel resource destroy from non-form context: POST + _method=DELETE + _token.
 * Second argument may be a raw token string or { _token: string } (jQuery-style).
 */
function del(url, tokenOrOpts) {
    const fd = new FormData();
    let token = csrfToken();
    if (typeof tokenOrOpts === 'string' && tokenOrOpts !== '') {
        token = tokenOrOpts;
    } else if (
        tokenOrOpts &&
        typeof tokenOrOpts === 'object' &&
        typeof tokenOrOpts._token === 'string'
    ) {
        token = tokenOrOpts._token;
    }
    fd.append('_token', token);
    fd.append('_method', 'DELETE');
    return unwrapData(apiClient.post(url, fd));
}

/**
 * Multipart POST (file inputs). Do not set Content-Type manually — browser sets boundary.
 * @param {string} url
 * @param {HTMLFormElement|FormData} formElementOrData
 * @param {import('axios').AxiosRequestConfig} [config]
 */
function postForm(url, formElementOrData, config) {
    const data =
        formElementOrData instanceof FormData
            ? formElementOrData
            : new FormData(formElementOrData);
    return unwrapData(
        apiClient.post(url, data, {
            ...config,
        })
    );
}

/**
 * POST as application/x-www-form-urlencoded (same as jQuery $.ajax with serialized form / plain object).
 * Pass body from jQuery: $('#form').serialize() or URLSearchParams string.
 */
function postUrlEncoded(url, body, config) {
    const data = typeof body === 'string' ? body : new URLSearchParams(body).toString();
    return unwrapData(
        apiClient.post(url, data, {
            ...config,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                ...(config && config.headers),
            },
        })
    );
}

const apiHttp = {
    instance: apiClient,
    get,
    post,
    put,
    patch,
    delete: del,
    postForm,
    postUrlEncoded,
    normalizeApiError,
    csrfToken,
};

if (typeof window !== 'undefined') {
    window.apiHttp = apiHttp;
}

module.exports = apiHttp;
