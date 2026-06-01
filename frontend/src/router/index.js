import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { 
    path: '/', 
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      { path: '', name: 'Home', component: () => import('@/views/public/HomeView.vue'), meta: { title: 'Home' } },
      { path: 'calls', name: 'PublicCalls', component: () => import('@/views/public/PublicCallsView.vue'), meta: { title: 'Research Calls' } },
      { path: 'calls/:id', name: 'PublicCallDetail', component: () => import('@/views/public/PublicCallDetailView.vue'), meta: { title: 'Call Details' } },
      { path: 'publications', name: 'PublicPublications', component: () => import('@/views/public/PublicPublicationsView.vue'), meta: { title: 'Publications' } },
      { path: 'events', name: 'PublicEvents', component: () => import('@/views/public/PublicEventsView.vue'), meta: { title: 'Events' } },
      { path: 'researchers', name: 'PublicResearchers', component: () => import('@/views/public/PublicResearchersView.vue'), meta: { title: 'Researchers' } },
      { path: 'community', name: 'PublicCommunity', component: () => import('@/views/public/PublicCommunityView.vue'), meta: { title: 'Community Impact' } }
    ]
  },
  { path: '/login', name: 'Login', component: () => import('@/views/auth/LoginView.vue'), meta: { guest: true, title: 'Login' } },
  { path: '/register', name: 'Register', component: () => import('@/views/auth/RegisterView.vue'), meta: { guest: true, title: 'Create Account' } },
  { path: '/forgot-password', name: 'ForgotPassword', component: () => import('@/views/auth/ForgotPasswordView.vue'), meta: { guest: true, title: 'Forgot Password' } },
  { path: '/reset-password/:token?', name: 'ResetPassword', component: () => import('@/views/auth/ResetPasswordView.vue'), meta: { guest: true, title: 'Reset Password' } },
  {
    path: '/app', // Abstract base path, bypassed by absolute child paths
    component: () => import('@/layouts/MainLayout.vue'), 
    meta: { requiresAuth: true },
    children: [
      { path: '/dashboard', name: 'Dashboard', component: () => import('@/views/DashboardView.vue'), meta: { title: 'Dashboard' } },
      { path: '/proposals', name: 'Proposals', component: () => import('@/views/proposals/ProposalListView.vue'), meta: { title: 'Proposals' } },
      { path: '/proposals/create', name: 'CreateProposal', component: () => import('@/views/proposals/CreateProposalView.vue'), meta: { title: 'Create Proposal', permissions: 'submit_proposals' } },
      { path: '/proposals/:id', name: 'ProposalDetail', component: () => import('@/views/proposals/ProposalDetailView.vue'), meta: { title: 'Proposal Detail' } },
      { path: 'reviewer/proposals', name: 'ReviewerProposals', component: () => import('@/views/reviewer/ReviewerProposalListView.vue'), meta: { title: 'My Reviews', roles: ['reviewer'] } },
      { path: 'reviewer/proposals/:id', name: 'ReviewerProposalDetail', component: () => import('@/views/reviewer/ReviewerProposalDetailView.vue'), meta: { title: 'Review Proposal', roles: ['reviewer'] } },
      { path: 'calls', name: 'Calls', component: () => import('@/views/calls/CallListView.vue'), meta: { title: 'Calls' } },
      { path: 'projects', name: 'Projects', component: () => import('@/views/projects/ProjectListView.vue'), meta: { title: 'Projects' } },
      { path: 'projects/create-from-proposal/:id', name: 'CreateProject', component: () => import('@/views/projects/CreateProjectView.vue'), meta: { title: 'Create Project' } },
      { path: 'projects/:id', name: 'ProjectDetail', component: () => import('@/views/projects/ProjectDetailView.vue'), meta: { title: 'Project Detail' } },
      { path: 'projects/:id/finance', name: 'ProjectFinance', component: () => import('@/views/projects/ProjectFinanceView.vue'), meta: { title: 'Project Finance' } },
      { path: 'publications', name: 'Publications', component: () => import('@/views/publications/PublicationListView.vue'), meta: { title: 'Publications' } },
      { path: 'events', name: 'Events', component: () => import('@/views/events/EventListView.vue'), meta: { title: 'Events' } },
      { path: 'partners', name: 'Partners', component: () => import('@/views/partners/PartnerListView.vue'), meta: { title: 'Partners' } },
      { path: 'outputs', name: 'Outputs', component: () => import('@/views/outputs/OutputListView.vue'), meta: { title: 'Outputs' } },
      { path: 'patents', name: 'Patents', component: () => import('@/views/patents/PatentListView.vue'), meta: { title: 'Patents' } },
      { path: 'users', name: 'Users', component: () => import('@/views/users/UserListView.vue'), meta: { title: 'Users', roles: ['super_admin', 'research_admin'] } },
      { path: 'community-problems', name: 'Community', component: () => import('@/views/community/CommunityProblemListView.vue'), meta: { title: 'Community' } },
      { path: 'reports', name: 'Reports', component: () => import('@/views/reports/ReportView.vue'), meta: { title: 'Reports', roles: ['super_admin', 'research_admin', 'director', 'department_head', 'finance_officer'] } },
      { path: 'settings', name: 'Settings', component: () => import('@/views/settings/SettingsView.vue'), meta: { title: 'Settings', roles: ['super_admin'] } },
      { path: 'profile', name: 'Profile', component: () => import('@/views/ProfileView.vue'), meta: { title: 'My Profile' } },
      { path: 'notifications', name: 'Notifications', component: () => import('@/views/notifications/NotificationView.vue'), meta: { title: 'Notifications' } },
      { path: 'proposals/:id/edit', name: 'EditProposal', component: () => import('@/views/proposals/ProposalEditView.vue'), meta: { title: 'Edit Proposal' } },
      { path: 'patents/:id/licenses', name: 'PatentLicenses', component: () => import('@/views/patents/LicenseListView.vue'), meta: { title: 'Licenses' } },
      { path: 'partners/:id/mous', name: 'PartnerMoUs', component: () => import('@/views/partners/MoUListView.vue'), meta: { title: 'MoUs' } },
      { path: 'audit-logs', name: 'AuditLogs', component: () => import('@/views/audit/AuditLogListView.vue'), meta: { title: 'Audit Logs', roles: ['super_admin','research_admin'] } },
      { path: 'files', name: 'Files', component: () => import('@/views/files/FileListView.vue'), meta: { title: 'Files' } },
      { path: 'users/:id', name: 'UserDetail', component: () => import('@/views/users/UserDetailView.vue'), meta: { title: 'User Detail', roles: ['super_admin','research_admin'] } },
      { path: 'roles', name: 'Roles', component: () => import('@/views/roles/RoleListView.vue'), meta: { title: 'Roles', roles: ['super_admin','research_admin'] } },
      { path: 'academic-years', name: 'AcademicYears', component: () => import('@/views/academic/AcademicYearListView.vue'), meta: { title: 'Academic Years' } },
      { path: 'research-centers', name: 'ResearchCenters', component: () => import('@/views/research/ResearchCenterListView.vue'), meta: { title: 'Research Centers' } },
      { path: 'expertise', name: 'Expertise', component: () => import('@/views/research/ExpertiseListView.vue'), meta: { title: 'Expertise' } },
      { path: 'review-criteria', name: 'ReviewCriteria', component: () => import('@/views/research/ReviewCriteriaListView.vue'), meta: { title: 'Review Criteria' } },
      { path: 'departments', name: 'Departments', component: () => import('@/views/academic/DepartmentListView.vue'), meta: { title: 'Departments' } },
      { path: 'universities', name: 'Universities', component: () => import('@/views/academic/UniversityListView.vue'), meta: { title: 'Universities' } },
      { path: 'events/:id', name: 'EventDetail', component: () => import('@/views/events/EventDetailView.vue'), meta: { title: 'Event Detail' } },
      { path: 'events/:id/attendance', name: 'EventAttendance', component: () => import('@/views/events/EventAttendanceView.vue'), meta: { title: 'Attendance', roles: ['super_admin','research_admin'] } },
      { path: 'partners/:id', name: 'PartnerDetail', component: () => import('@/views/partners/PartnerDetailView.vue'), meta: { title: 'Partner Detail' } },
      { path: 'outputs/:id', name: 'OutputDetail', component: () => import('@/views/outputs/OutputDetailView.vue'), meta: { title: 'Output Detail' } },
      { path: 'publications/:id', name: 'PublicationDetail', component: () => import('@/views/publications/PublicationDetailView.vue'), meta: { title: 'Publication Detail' } },
      { path: 'patents/:id', name: 'PatentDetail', component: () => import('@/views/patents/PatentDetailView.vue'), meta: { title: 'Patent Detail' } },
      { path: 'faculties', name: 'Faculties', component: () => import('@/views/academic/FacultyListView.vue'), meta: { title: 'Faculties' } },
      { path: 'campuses', name: 'Campuses', component: () => import('@/views/academic/CampusListView.vue'), meta: { title: 'Campuses' } },
      { path: 'expenses', name: 'Expenses', component: () => import('@/views/finance/ExpenseListView.vue'), meta: { title: 'Expenses', roles: ['finance_officer','super_admin'] } },
      { path: 'finance-checks', name: 'FinanceChecks', component: () => import('@/views/finance/FinanceCheckListView.vue'), meta: { title: 'Finance Checks', roles: ['finance_officer','super_admin','research_admin'] } },
      { path: 'ethics-requests', name: 'EthicsRequests', component: () => import('@/views/ethics/EthicsRequestListView.vue'), meta: { title: 'Ethics Requests', roles: ['ethics_officer','super_admin','research_admin'] } },
      { path: 'detection-requests', name: 'DetectionRequests', component: () => import('@/views/detection/DetectionRequestListView.vue'), meta: { title: 'Detection', roles: ['super_admin','research_admin'] } },
      { path: 'thematic-areas', name: 'ThematicAreas', component: () => import('@/views/research/ThematicAreaListView.vue'), meta: { title: 'Thematic Areas' } },
      { path: 'permissions', name: 'Permissions', component: () => import('@/views/permissions/PermissionListView.vue'), meta: { title: 'Permissions', roles: ['super_admin','research_admin'] } },
      { path: 'calls/:id', name: 'CallDetail', component: () => import('@/views/calls/CallDetailView.vue'), meta: { title: 'Call Detail' } },
      { path: 'agreement-files', name: 'AgreementFiles', component: () => import('@/views/agreements/AgreementFileListView.vue'), meta: { title: 'Agreement Files' } },
      { path: 'search', name: 'Search', component: () => import('@/views/search/GlobalSearchView.vue'), meta: { title: 'Search' } },
      { path: 'language', name: 'LanguagePreference', component: () => import('@/views/language/LanguagePreferenceView.vue'), meta: { title: 'Language Preference' } },
      { path: '403', name: 'Forbidden', component: () => import('@/views/errors/ForbiddenView.vue'), meta: { title: 'Access Denied' } },
    ]
  },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: () => import('@/views/errors/NotFoundView.vue'), meta: { title: 'Not Found' } },
]

const router = createRouter({ history: createWebHistory(), routes, scrollBehavior() { return { top: 0 } } })

router.beforeEach((to, from) => {
  const auth = useAuthStore()
  document.title = to.meta.title ? `${to.meta.title} – RDRIMS` : 'RDRIMS'
  
  if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
  if (to.meta.guest && auth.isAuthenticated) return '/dashboard'
  if (to.meta.roles?.length && !auth.hasRole(...to.meta.roles)) return '/dashboard'
  if (to.meta.permissions && !auth.hasPermission(to.meta.permissions)) return '/dashboard'
})

export default router
