import { defineStore } from 'pinia'
import api from '@/services/api'

export const useLookupStore = defineStore('lookup', {
  state: () => ({
    // Settings
    settings: {},
    
    // Lookups
    callStatuses: [],
    proposalTypes: [],
    proposalStatuses: [],
    reviewDecisions: [],
    financeCheckStatuses: [],
    ethicsApprovalStatuses: [],
    patentStatuses: [],
    communityProblemStatuses: [],
    projectStatuses: [],
    milestoneStatuses: [],
    taskStatuses: [],
    investigatorRoles: [],
    invitationStatuses: [],
    agreementTypes: [],
    outputCategories: [],
    studentLevels: [],
    outputSubtypes: [],
    detectionServices: [],
    detectionStatuses: [],
    participantTypes: [],
    outputStatuses: [],
    
    _hasLoaded: false
  }),
  
  getters: {
    getSetting: (state) => (key, defaultValue = null) => {
      return state.settings[key] !== undefined ? state.settings[key] : defaultValue;
    }
  },
  
  actions: {
    async initialize() {
      if (this._hasLoaded) return;
      
      try {
        // Fetch settings
        const settingsRes = await api.get('/settings');
        const settingsMap = {};
        if (Array.isArray(settingsRes.data)) {
          settingsRes.data.forEach(s => settingsMap[s.key] = s.value);
        } else if (settingsRes.data?.data && Array.isArray(settingsRes.data.data)) {
           settingsRes.data.data.forEach(s => settingsMap[s.key] = s.value);
        } else {
           Object.assign(settingsMap, settingsRes.data);
        }
        this.settings = settingsMap;
        
        // Mark as loaded so we don't spam the server on every route change
        this._hasLoaded = true;
        
        // We can fetch critical lookups immediately, or lazily.
        // For a full robust app, lazy fetching is better, but since it's 
        // requested to just "populate from lookup", we'll implement a helper.
      } catch (err) {
        console.error('Failed to initialize lookup store', err);
      }
    },
    
    async fetchLookup(table) {
      // camelCase representation of the table name for state
      const stateKey = table.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
      
      // If we already have it, don't fetch again unless forced
      // (Simplified caching mechanism)
      if (this[stateKey] && this[stateKey].length > 0) return this[stateKey];
      
      try {
        const { data } = await api.get(`/lookups/${table}`);
        const items = Array.isArray(data) ? data : (data.data || []);
        if (this[stateKey] !== undefined) {
          this[stateKey] = items;
        }
        return items;
      } catch (e) {
        console.error(`Failed to fetch lookup: ${table}`, e);
        return [];
      }
    }
  }
})
