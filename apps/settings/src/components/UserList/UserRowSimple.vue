<template>
	<div
		class="row"
		:class="{'disabled': loading.delete || loading.disable}"
		:data-id="user.id">
		<div class="avatar" :class="{'icon-loading-small': loading.delete || loading.disable || loading.wipe}">
			<img v-if="!loading.delete && !loading.disable && !loading.wipe"
				alt=""
				width="32"
				height="32"
				:src="generateAvatar(user.id, 32)"
				:srcset="generateAvatar(user.id, 64)+' 2x, '+generateAvatar(user.id, 128)+' 4x'">
		</div>
		<!-- dirty hack to ellipsis on two lines -->
		<div class="name">
			{{ user.id }}
			<div class="displayName">
				{{ user.displayname }}
			</div>
		</div>
		<div />
		<div class="mailAddress">
			{{ user.email }}
		</div>
		<div class="groups">
			{{ userGroupsLabels }}
		</div>
		<div v-if="subAdminsGroups.length>0 && settings.isAdmin" class="subAdminsGroups">
			{{ userSubAdminsGroupsLabels }}
		</div>
		<div class="quota">
			<progress class="quota-user-progress"
				:class="{'warn':usedQuota>80}"
				:value="usedQuota"
				max="100" />
		</div>
		<div v-if="showConfig.showLanguages" class="languages">
			{{ userLanguage.name }}
		</div>
		<div v-if="showConfig.showStoragePath" class="storageLocation">
			{{ user.storageLocation }}
		</div>
		<div v-if="showConfig.showUserBackend" class="userBackend">
			{{ user.backend }}
		</div>
		<div v-if="showConfig.showLastLogin" class="userLastLogin">
			{{ user.lastLogin>0 ? OC.Util.relativeModifiedDate(user.lastLogin) : t('settings','Never') }}
		</div>
		<div class="userActions">
			<div v-if="OC.currentUser !== user.id && user.id !== 'admin' && !loading.all" class="toggleUserActions">
				<Actions>
					<ActionButton icon="icon-rename" @click="$emit('edit')">
						{{ t('settings', 'Edit User') }}
					</ActionButton>
				</Actions>
				<div v-click-outside="hideMenu" class="icon-more" @click="$emit('toggleMenu')" />
				<div class="popovermenu" :class="{ 'open': openedMenu }">
					<PopoverMenu :menu="userActions" />
				</div>
			</div>
			<div class="feedback" :style="{opacity: feedbackMessage !== '' ? 1 : 0}">
				<div class="icon-checkmark" />
				{{ feedbackMessage }}
			</div>
		</div>
	</div>
</template>

<script>
import { PopoverMenu, Actions, ActionButton } from 'nextcloud-vue'
import ClickOutside from 'vue-click-outside'

export default {
	name: 'UserRowSimple',
	components: {
		PopoverMenu,
		ActionButton,
		Actions
	},
	directives: {
		ClickOutside
	},
	props: {
		user: {
			type: Object,
			required: true
		},
		loading: {
			type: Object,
			required: true
		},
		generateAvatar: {
			type: Function,
			required: true
		},
		showConfig: {
			type: Object,
			required: true
		},
		userGroups: {
			type: Array,
			required: true
		},
		userSubAdminsGroups: {
			type: Array,
			required: true
		},
		userLanguage: {
			type: Object,
			required: true
		},
		usedQuota: {
			type: Number,
			required: true
		},
		userLastLogin: {
			type: Object,
			required: true
		},
		userActions: {
			type: Array,
			required: true
		},
		openedMenu: {
			type: Boolean,
			required: true
		},
		feedbackMessage: {
			type: String,
			required: true
		},
		subAdminsGroups: {
			type: Array,
			required: true
		},
		settings: {
			type: Object,
			required: true
		}
	},
	computed: {
		userGroupsLabels() {
			return this.userGroups
				.map((groupObject) => groupObject.name)
				.join(', ')
		},
		userSubAdminsGroupsLabels() {
			return this.userSubAdminsGroups
				.map((groupObject) => groupObject.name)
				.join(', ')
		}

	},
	methods: {
		hideMenu() {
			this.$emit('hideMenu')
		}
	}
}
</script>

<style scoped>

</style>
