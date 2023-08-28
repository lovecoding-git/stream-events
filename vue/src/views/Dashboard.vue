<template class="w-full">
  <PageComponent title="Dashboard">
    <div v-if="loading" class="flex justify-center">Loading...</div>
    <div v-else>
      <div class="block text-gray-700 w-full">
        <div class="text-gray-600 text-center py-2 font-bold">
          Stream Events
        </div>
        <div class="block text-gray-700 px-10">
          <ul ref="scrollContainer" @scroll="checkScrollEnd" class="overflow-y-scroll max-h-[600px]">
            <li v-for="(event, index) in events" :key="event.id"
              :class="[event.is_read ? 'bg-white' : 'bg-light-blue-200', 'border', 'border-gray-300', 'border-t', 'border-b', 'mb-2', 'cursor-pointer']"
                @click="markEventAsRead(event.id, event.model_name)">
              {{ index }} {{event.id}} {{ event.name }} {{ event.model_name }}
            </li>
            <div v-if="eventsLoading" class="flex justify-center mt-2">Loading...</div>
          </ul>
        </div>
      </div>

    </div>

  </PageComponent>
</template>

<script setup>
import PageComponent from "../components/PageComponent.vue";
import { computed, ref, onMounted, watch } from "vue";
import { useStore } from "vuex";

const store = useStore();

onMounted(() => {
  store.dispatch("loadMoreEvents", 100); // Load the initial 100 items
});

const loading = computed(() => store.state.dashboard.loading);
const data = computed(() => store.state.dashboard.data);
const events = computed(() => store.state.events.data); // Assuming you store events in this state
const eventsLoading = computed(() => store.state.events.loading);  // Assuming you store events in this state

store.dispatch("getDashboardData");

function checkScrollEnd(event) {
  const { target } = event;
  if (target.scrollTop + target.clientHeight >= target.scrollHeight - 10 && !eventsLoading.value) {
    store.dispatch("loadMoreEvents", 50);
  }
}

function markEventAsRead(id, model) {
  store.dispatch("markEventAsRead", { id, model });
}

</script>

<style scoped>
ul {
  max-height: 400px;
  overflow-y: auto;
}
</style>
