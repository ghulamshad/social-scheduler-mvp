<template>
  <div class="team-manager">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
        Team Management
      </h2>
      <p class="text-gray-600 dark:text-gray-400">
        Manage your teams and collaborate on content
      </p>
    </div>

    <!-- Create Team Button -->
    <div class="mb-6">
      <button
        @click="showCreateModal = true"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <PlusOutlined class="w-4 h-4 mr-2" />
        Create New Team
      </button>
    </div>

    <!-- Teams List -->
    <div v-if="teams.length > 0" class="space-y-6">
      <div
        v-for="team in teams"
        :key="team.id"
        class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div v-if="team.logo_url" class="w-10 h-10 rounded-full overflow-hidden">
              <img :src="team.logo_url" :alt="team.name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
              <TeamOutlined class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ team.name }}
              </h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ team.description || 'No description' }}
              </p>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">
              {{ team.members?.length || 0 }} members
            </span>
            <button
              @click="viewTeam(team)"
              class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              View
            </button>
          </div>
        </div>

        <!-- Team Stats -->
        <div class="grid grid-cols-3 gap-4 mb-4">
          <div class="text-center">
            <div class="text-lg font-semibold text-blue-600">{{ team.posts?.length || 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Posts</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-semibold text-green-600">{{ getPendingApprovals(team) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-semibold text-purple-600">{{ getMemberRole(team) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Your Role</div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex space-x-2">
          <button
            @click="inviteMember(team)"
            class="px-3 py-1 text-sm bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800 transition-colors"
          >
            Invite Member
          </button>
          <button
            v-if="canManageTeam(team)"
            @click="editTeam(team)"
            class="px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors"
          >
            Edit Team
          </button>
          <button
            v-if="!isTeamOwner(team)"
            @click="leaveTeam(team)"
            class="px-3 py-1 text-sm bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800 transition-colors"
          >
            Leave Team
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <TeamOutlined class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
        No teams yet
      </h3>
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Create your first team to start collaborating on content
      </p>
      <button
        @click="showCreateModal = true"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        Create Team
      </button>
    </div>

    <!-- Create Team Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Create New Team
        </h3>
        
        <form @submit.prevent="createTeam" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Team Name
            </label>
            <input
              v-model="createForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="Enter team name"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Description
            </label>
            <textarea
              v-model="createForm.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="Describe your team's purpose"
            ></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Subdomain (Optional)
            </label>
            <input
              v-model="createForm.subdomain"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="your-team"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              For white-label branding (e.g., your-team.socialscheduler.com)
            </p>
          </div>
          
          <div class="flex space-x-3">
            <button
              type="button"
              @click="showCreateModal = false"
              class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="creating"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <LoadingOutlined v-if="creating" class="w-4 h-4 animate-spin mr-2" />
              {{ creating ? 'Creating...' : 'Create Team' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Team Details Modal -->
    <div v-if="showTeamModal && selectedTeam" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ selectedTeam.name }}
          </h3>
          <button
            @click="showTeamModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <CloseOutlined class="w-5 h-5" />
          </button>
        </div>
        
        <!-- Team Info -->
        <div class="mb-6">
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ selectedTeam.description || 'No description' }}
          </p>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ selectedTeam.posts?.length || 0 }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Total Posts</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ selectedTeam.members?.length || 0 }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Members</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">{{ getPendingApprovals(selectedTeam) }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Pending Approvals</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-orange-600">{{ getMemberRole(selectedTeam) }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Your Role</div>
            </div>
          </div>
        </div>
        
        <!-- Members List -->
        <div class="mb-6">
          <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">
            Team Members
          </h4>
          
          <div class="space-y-2">
            <div
              v-for="member in selectedTeam.members"
              :key="member.id"
              class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                  <UserOutlined class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">
                    {{ member.user?.name }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ member.user?.email }}
                  </div>
                </div>
              </div>
              
              <div class="flex items-center space-x-2">
                <span class="px-2 py-1 text-xs rounded-full"
                      :class="getRoleBadgeClass(member.role)">
                  {{ member.role }}
                </span>
                
                <button
                  v-if="canManageTeam(selectedTeam) && member.role !== 'owner'"
                  @click="updateMemberRole(member)"
                  class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                  Change Role
                </button>
                
                <button
                  v-if="canManageTeam(selectedTeam) && member.role !== 'owner'"
                  @click="removeMember(member)"
                  class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Recent Posts -->
        <div>
          <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">
            Recent Posts
          </h4>
          
          <div v-if="selectedTeam.posts?.length > 0" class="space-y-2">
            <div
              v-for="post in selectedTeam.posts.slice(0, 5)"
              :key="post.id"
              class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
            >
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <p class="text-sm text-gray-900 dark:text-white truncate">
                    {{ post.content }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(post.schedule_time) }} • {{ post.status }}
                  </p>
                </div>
                <div class="flex items-center space-x-2">
                  <span v-if="post.approval_status === 'pending_approval'"
                        class="px-2 py-1 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full">
                    Pending
                  </span>
                  <span v-else-if="post.approval_status === 'approved'"
                        class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                    Approved
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
            No posts yet
          </div>
        </div>
      </div>
    </div>

    <!-- Invite Member Modal -->
    <div v-if="showInviteModal && selectedTeam" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Invite Member to {{ selectedTeam.name }}
        </h3>
        
        <form @submit.prevent="inviteMemberToTeam" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email Address
            </label>
            <input
              v-model="inviteForm.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="Enter email address"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Role
            </label>
            <select
              v-model="inviteForm.role"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
            >
              <option value="viewer">Viewer (Read-only)</option>
              <option value="editor">Editor (Create & Edit)</option>
              <option value="admin">Admin (Manage team)</option>
            </select>
          </div>
          
          <div class="flex space-x-3">
            <button
              type="button"
              @click="showInviteModal = false"
              class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="inviting"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <LoadingOutlined v-if="inviting" class="w-4 h-4 animate-spin mr-2" />
              {{ inviting ? 'Inviting...' : 'Send Invite' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { 
  PlusOutlined, 
  TeamOutlined, 
  UserOutlined, 
  CloseOutlined, 
  LoadingOutlined 
} from '@ant-design/icons-vue';
import { api } from '@/services/api';

// Types
interface TeamMember {
  id: number;
  user: {
    id: number;
    name: string;
    email: string;
  };
  role: string;
  joined_at: string;
}

interface Team {
  id: number;
  name: string;
  description: string;
  logo_url: string;
  subdomain: string;
  owner_id: number;
  members: TeamMember[];
  posts: any[];
}

// State
const teams = ref<Team[]>([]);
const selectedTeam = ref<Team | null>(null);
const showCreateModal = ref(false);
const showTeamModal = ref(false);
const showInviteModal = ref(false);
const creating = ref(false);
const inviting = ref(false);

// Forms
const createForm = reactive({
  name: '',
  description: '',
  subdomain: '',
});

const inviteForm = reactive({
  email: '',
  role: 'viewer',
});

// Methods
const loadTeams = async () => {
  try {
    const response = await api.get('/teams');
    teams.value = response.data.data;
  } catch (error) {
    console.error('Failed to load teams:', error);
  }
};

const createTeam = async () => {
  creating.value = true;
  try {
    await api.post('/teams', createForm);
    showCreateModal.value = false;
    Object.assign(createForm, { name: '', description: '', subdomain: '' });
    await loadTeams();
  } catch (error) {
    console.error('Failed to create team:', error);
  } finally {
    creating.value = false;
  }
};

const viewTeam = (team: Team) => {
  selectedTeam.value = team;
  showTeamModal.value = true;
};

const inviteMember = (team: Team) => {
  selectedTeam.value = team;
  showInviteModal.value = true;
};

const inviteMemberToTeam = async () => {
  inviting.value = true;
  try {
    await api.post(`/teams/${selectedTeam.value?.id}/invite`, inviteForm);
    showInviteModal.value = false;
    Object.assign(inviteForm, { email: '', role: 'viewer' });
    await loadTeams();
  } catch (error) {
    console.error('Failed to invite member:', error);
  } finally {
    inviting.value = false;
  }
};

const editTeam = (team: Team) => {
  // TODO: Implement edit team functionality
  console.log('Edit team:', team);
};

const leaveTeam = async (team: Team) => {
  if (confirm(`Are you sure you want to leave ${team.name}?`)) {
    try {
      await api.post(`/teams/${team.id}/leave`);
      await loadTeams();
    } catch (error) {
      console.error('Failed to leave team:', error);
    }
  }
};

const updateMemberRole = (member: TeamMember) => {
  // TODO: Implement role update functionality
  console.log('Update member role:', member);
};

const removeMember = async (member: TeamMember) => {
  if (confirm(`Are you sure you want to remove ${member.user.name} from the team?`)) {
    try {
      await api.delete(`/teams/${selectedTeam.value?.id}/members/${member.id}`);
      await loadTeams();
    } catch (error) {
      console.error('Failed to remove member:', error);
    }
  }
};

const getMemberRole = (team: Team): string => {
  const member = team.members?.find(m => m.user.id === 1); // TODO: Get current user ID
  return member?.role || 'Unknown';
};

const canManageTeam = (team: Team): boolean => {
  const role = getMemberRole(team);
  return ['owner', 'admin'].includes(role);
};

const isTeamOwner = (team: Team): boolean => {
  return getMemberRole(team) === 'owner';
};

const getPendingApprovals = (team: Team): number => {
  return team.posts?.filter(p => p.approval_status === 'pending_approval').length || 0;
};

const getRoleBadgeClass = (role: string): string => {
  const classes = {
    owner: 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200',
    admin: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    editor: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
    viewer: 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200',
  };
  return classes[role as keyof typeof classes] || classes.viewer;
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString();
};

// Lifecycle
onMounted(() => {
  loadTeams();
});
</script> 