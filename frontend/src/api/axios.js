import axios from 'axios';

// Dynamically use the Vite environment variable, fallback to localhost
const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const api = axios.create({
    baseURL: `${baseURL}/api`,
    withCredentials: true, // Required for Laravel Sanctum cookies
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// Request Interceptor
api.interceptors.request.use(config => {
    // If you are using Bearer tokens instead of (or alongside) cookies
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

// Response Interceptor
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Handle 401 Unauthorized (Session/Token Expired)
            if (error.response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                // Optional: Redirect to login page if you are using vue-router
                // window.location.href = '/login';
            }
            
            // Handle 419 Page Expired (CSRF Token Mismatch)
            if (error.response.status === 419) {
                console.warn('CSRF token mismatch. Refreshing token...');
                // Usually handled by refreshing the page or re-fetching CSRF
            }
        }
        return Promise.reject(error);
    }
);

// CSRF Protection for Laravel Sanctum (Session/Cookie Auth)
export const getCsrfToken = () => {
    return axios.get(`${baseURL}/sanctum/csrf-cookie`, {
        withCredentials: true 
    });
};

export default api;
