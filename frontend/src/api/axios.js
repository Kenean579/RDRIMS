import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// CSRF Protection for Laravel Sanctum
export const getCsrfToken = () => {
    return api.get('/csrf-cookie', { baseURL: 'http://localhost:8000' });
};

export default api;
