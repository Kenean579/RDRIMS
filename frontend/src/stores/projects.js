// stores/projects.js
import { defineStore } from 'pinia';
import api from '@/services/api';

export const useProjectStore = defineStore('projects', {
    state: () => ({
        projects: [],
        currentProject: null,
        milestones: [],
        tasks: [],
        expenses: [],
        loading: false,
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
        },
    }),
    actions: {
        async fetchProjects(params = {}) {
            this.loading = true;
            try {
                const { data } = await api.get('/projects', { params });
                this.projects = data.data || [];
                this.pagination = {
                    current_page: data.meta?.current_page || 1,
                    last_page: data.meta?.last_page || 1,
                    total: data.meta?.total || 0,
                };
            } catch (error) {
                console.error('Failed to fetch projects:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async fetchProject(id) {
            this.loading = true;
            try {
                const { data } = await api.get(`/projects/${id}`);
                this.currentProject = data;
                return data;
            } catch (error) {
                console.error('Failed to fetch project:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async createProject(payload) {
            const { data } = await api.post('/projects', payload);
            return data;
        },
        async updateProject(id, payload) {
            const { data } = await api.put(`/projects/${id}`, payload);
            return data;
        },
        async submitProject(id) {
            const { data } = await api.post(`/projects/${id}/submit`);
            return data;
        },
        async approveProject(id, comments = null) {
            const { data } = await api.post(`/projects/${id}/approve`, { comments });
            return data;
        },
        async completeProject(id) {
            const { data } = await api.post(`/projects/${id}/complete`);
            return data;
        },
        async addInvestigator(projectId, userId, role) {
            const { data } = await api.post(`/projects/${projectId}/investigators`, { user_id: userId, role });
            return data;
        },
        async removeInvestigator(projectId, investigatorId) {
            await api.delete(`/projects/${projectId}/investigators/${investigatorId}`);
        },
        async addExpense(projectId, payload) {
            const { data } = await api.post(`/projects/${projectId}/expenses`, payload);
            return data;
        },
    },
});