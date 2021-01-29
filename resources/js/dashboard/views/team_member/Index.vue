<template>
<div>
  <loading-indicator v-if="isLoading"></loading-indicator>
  <div :class="isFetched ? 'is-loaded' : 'is-loading'">
    <header class="content-header">
      <h1>Teams</h1>
      <router-link :to="{ name: 'team-member-create' }" class="feather-icon feather-icon--prepend">
        <plus-icon size="16"></plus-icon>
        <span>Hinzufügen</span>
      </router-link>
    </header>

    <div v-for="(team, index) in groupedTeams" :key="index" class="sa-md">

      <div class="listing" v-if="team.length">
        <draggable 
          :disabled="false"
          v-model="groupedTeams[index]" 
          @end="order(index)"
          ghost-class="draggable-ghost"
          draggable=".listing__item">
          <div
            :class="[t.publish == 0 ? 'is-disabled' : '', 'listing__item is-draggable']"
            v-for="t in team"
            :key="t.id"
          >
            <div class="listing__item-body">
              {{ t.firstname }} {{ t.name}} <separator /> {{ t.team.category.name}}
            </div>
            <list-actions 
              :id="t.id" 
              :record="t"
              :isDraggable="false"
              :routes="{edit: 'team-member-edit'}">
            </list-actions>
          </div>
        </draggable>
      </div>

      <div v-else>
        <p class="no-records">Es sind noch keine Inhalte vorhanden...</p>
      </div>
    </div>
  </div>
</div>
</template>
<script>

// Icons
import { PlusIcon } from 'vue-feather-icons';

// Components
import ListActions from "@/components/ui/ListActions.vue";
import draggable from "vuedraggable";

// Mixins
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";

export default {

  components: {
    ListActions,
    PlusIcon,
    draggable,
  },

  mixins: [ErrorHandling, Helpers],

  data() {
    return {
      isLoading: false,
      isFetched: false,
      teamMembers: [],
      groupedTeams: [],
    };
  },

  created() {
    this.fetch();
  },

  methods: {

    fetch() {
      this.axios.get(`/api/team/members`).then(response => {
        this.teamMembers = response.data.data;
        this.isFetched = true;
        this.groupedTeams = _.groupBy(this.teamMembers, "team_id");
      });
    },

    toggle(id,event) {
      let uri = `/api/team/member/state/${id}`;
      this.isLoading = true;
      this.axios.get(uri).then(response => {
        const index = this.teamMembers.findIndex(x => x.id === id);
        this.teamMembers[index].publish = response.data;
        this.$notify({ type: "success", text: "Status geändert" });
        this.isLoading = false;
      });
    },

    destroy(id, event) {
      if (confirm("Bitte löschen bestätigen!")) {
        let uri = `/api/team/member/${id}`;
        this.isLoading = true;
        this.axios.delete(uri).then(response => {
          this.fetch();
          this.isLoading = false;
        });
      }
    },

    order(groupIndex) {
      let members = this.groupedTeams[groupIndex].map(function(member, index) {
        member.order = index;
        return member;
      });

      if (this.debounce) return;
      this.debounce = setTimeout(
        function(members) {
          this.debounce = false;
          let uri = `/api/team/member/order`;
          this.axios.post(uri, { members: members }).then(response => {
            this.fetch();
            this.$notify({ type: "success", text: "Reihenfolge angepasst" });
          });
        }.bind(this, members),
        500
      );
    }
  }
}
</script>