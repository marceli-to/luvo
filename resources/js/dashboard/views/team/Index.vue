<template>
  <div>
    <LoadingIndicator v-if="isLoading" />
    <div :class="isFetched ? 'is-loaded' : 'is-loading'">
      <header class="content-header">
        <h1>Teams</h1>
        <div v-if="teams.items.length < 2">
          <router-link :to="{ name: 'team-create' }" class="feather-icon feather-icon--prepend">
            <PlusIcon size="16" />
            <span>Hinzufügen</span>
          </router-link>
        </div>
      </header>
      <div class="listing" v-if="teams.items.length">
        <div
          v-for="team in teams.items"
          :key="team.id"
          :class="[team.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        >
          <div class="listing__item-body">
            {{ team.title.de }} <Separator /> <span v-if="team.slug">Team {{ capitalizeFirst(team.slug) }}</span>
          </div>
          <ListActions :record="team" edit-route="team-edit" @toggle="teams.toggle" @destroy="teams.destroy" />
        </div>
      </div>
      <div v-else>
        <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
      </div>

      <header class="content-header sb-lg">
        <h1>Mitarbeiter</h1>
        <router-link :to="{ name: 'team-member-create' }" class="feather-icon feather-icon--prepend">
          <PlusIcon size="16" />
          <span>Hinzufügen</span>
        </router-link>
      </header>
      <div v-if="members.items.length">
        <div v-for="(group, teamId) in memberGroups" :key="teamId">
          <div class="listing is-grouped">
            <draggable
              v-model="memberGroups[teamId]"
              item-key="id"
              ghost-class="draggable-ghost"
              draggable=".listing__item"
              @end="orderMembers(memberGroups[teamId])"
            >
              <template #item="{ element: member }">
                <div :class="[member.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']">
                  <div class="listing__item-body">
                    {{ member.firstname }} {{ member.name }} <Separator /> <span v-if="member.team.slug">Team {{ capitalizeFirst(member.team.slug) }}</span>
                  </div>
                  <ListActions :record="member" edit-route="team-member-edit" @toggle="members.toggle" @destroy="members.destroy" />
                </div>
              </template>
            </draggable>
          </div>
        </div>
      </div>
      <div v-else>
        <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
      </div>

      <header class="content-header sb-lg">
        <h1>Assistenz</h1>
        <div v-if="assistants.items.length < 2">
          <router-link :to="{ name: 'assistant-create' }" class="feather-icon feather-icon--prepend">
            <PlusIcon size="16" />
            <span>Hinzufügen</span>
          </router-link>
        </div>
      </header>
      <div class="listing" v-if="assistants.items.length">
        <div
          v-for="assistant in assistants.items"
          :key="assistant.id"
          :class="[assistant.publish == 0 ? 'is-disabled' : '', 'listing__item']"
        >
          <div class="listing__item-body">
            <span v-if="assistant.team.slug">Team {{ capitalizeFirst(assistant.team.slug) }}</span>
          </div>
          <ListActions :record="assistant" edit-route="assistant-edit" @toggle="assistants.toggle" @destroy="assistants.destroy" />
        </div>
      </div>
      <div v-else>
        <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, computed } from 'vue';
import draggable from 'vuedraggable';
import { PlusIcon } from 'lucide-vue-next';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import ListActions from '@/components/ui/ListActions.vue';
import Separator from '@/components/ui/Separator.vue';
import { useListing } from '@/composables/useListing';
import { useOrder } from '@/composables/useOrder';
import { capitalizeFirst, groupBy } from '@/lib/utils';

const isLoading = ref(false);

const teams = reactive(useListing({ list: '/api/team', resource: 'team', isLoading }));

// Members are ordered within their team
const memberGroups = ref({});
const members = reactive(useListing({
  list: '/api/team/members',
  resource: 'team/member',
  isLoading,
  loaded: items => memberGroups.value = groupBy(items, 'team_id'),
}));
const orderMembers = useOrder({ url: '/api/team/member/order', key: 'members', saved: members.fetch });

const assistants = reactive(useListing({ list: '/api/assistants', resource: 'assistant', isLoading }));

const isFetched = computed(() => teams.isFetched && assistants.isFetched);
</script>
