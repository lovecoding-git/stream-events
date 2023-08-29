<template class="w-full">
  <PageComponent title="Dashboard">
    <div v-if="loading" class="flex justify-center">Loading...</div>
    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 text-gray-700">
        <DashboardCard class="order-1 lg:order-2 border min-h-[150px]" style="animation-delay: 0.1s">
          <div class="text-gray-600 text-center py-2 font-bold">
            Top 3 Best Sales
          </div>
          <div
            class="text-2xl pb-4 font-semibold flex-1 flex items-center justify-center"
          >
            <ul>
              <li v-for="item in data.topSellingItems" :key="item.item_name" class="block">
                {{ item.item_name }} - {{ Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(item.total_sales) }}
              </li>
            </ul>
          </div>
        </DashboardCard>
        <DashboardCard class="order-2 lg:order-4 border min-h-[150px]" style="animation-delay: 0.2s">
          <div class="text-gray-600 text-center py-2 font-bold">
            Followers
          </div>
          <div class="text-4xl text-gray-600 text-center ">
            {{ data.totalFollowers ?? 0 }}
          </div>
        </DashboardCard>
        <DashboardCard class="order-3 lg:order-1 border block min-h-[150px]" style="animation-delay: 0.2s">
          <div class="text-gray-600 text-center py-2 font-bold">
            Total Revenue
          </div>
          <div class="text-4xl text-gray-600 text-center ">
            {{ Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data.totalRevenue ?? 0) }}
          </div>
        </DashboardCard>
      </div>
      <div class="block text-gray-700 w-full">
        <div class="text-gray-600 text-center py-2 font-bold">
          Stream Events
        </div>
        <div class="block text-gray-700 px-10">
          <ul ref="scrollContainer" @scroll="checkScrollEnd" class="overflow-y-scroll max-h-[600px]">
            <li v-for="(event, index) in events" :key="event.id"
              :class="[event.is_read ? 'bg-white' : 'bg-light-blue-200', 'border', 'border-gray-300', 'border-t', 'border-b', 'mb-2', 'cursor-pointer']"
                @click="markEventAsRead(event.id, event.model_name)">
                {{ renderEventMessage(event, index) }}
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
const events = computed(() => store.state.events.data);
const eventsLoading = computed(() => store.state.events.loading);

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

function renderEventMessage(event, index) {
    switch (event.model_name) {
      case 'Follower':
        return `${index}. ${event.name} followed you!`;
      case 'Subscriber':
        return `${index}. ${event.name} (${event.tier}) subscribed to you!`;
      case 'Donation':
        return `${index}. ${event.name} donated ${event.amount} ${event.currency} to you! \n “Thank you for being awesome”`;
      case 'MerchSale':
        return `${index}. ${event.name} bought ${event.item_name} from you for ${event.price} USD!`;
      default:
        return '';
    }
  }

</script>

<style scoped>
ul {
  max-height: 400px;
  overflow-y: auto;
}
</style>
