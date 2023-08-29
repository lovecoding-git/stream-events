import { createStore } from "vuex";
import axiosClient from "../axios";

const store = createStore({
  state: {
    user: {
      data: {},
      token: sessionStorage.getItem("TOKEN"),
    },
    dashboard: {
      loading: false,
      data: {}
    },
    surveys: {
      loading: false,
      links: [],
      data: []
    },
    currentSurvey: {
      data: {},
      loading: false,
    },
    questionTypes: ["text", "select", "radio", "checkbox", "textarea"],
    notification: {
      show: false,
      type: 'success',
      message: ''
    },
    events: {
      loading: false,
      data: [],
      currentPage: 1,
      firstPage: 1,
      lastPage: null,
      hasMore: true,
    },
  },
  getters: {},
  actions: {

    register({commit}, user) {
      return axiosClient.post('/register', user)
        .then(({data}) => {
          commit('setUser', data.user);
          commit('setToken', data.token)
          return data;
        })
    },
    login({commit}, user) {
      return axiosClient.post('/login', user)
        .then(({data}) => {
          commit('setUser', data.user);
          commit('setToken', data.token)
          return data;
        })
    },
    logout({commit}) {
      return axiosClient.post('/logout')
        .then(response => {
          commit('logout')
          return response;
        })
    },
    async socialLogin({ commit }) {
      try {
        const response = await axiosClient.get('/redirect/google');
        window.location.href = response.data.url;  // This will redirect the user to Oauth's login page
      } catch (err) {
        throw new Error(err.response.data.error);
      }
    },
    storeTokenAndUser({commit}, token) {
      commit('setToken', token);
      // Use the token to fetch the user's details from the backend, then commit it
      axiosClient.get('/user', { headers: { 'Authorization': `Bearer ${token}` } })
          .then(response => {
              commit('setUser', response.data);
          })
          .catch(error => {
              console.error('Error fetching user details:', error);
          });
  },
    getUser({commit}) {
      return axiosClient.get('/user')
      .then(res => {
        console.log(res);
        commit('setUser', res.data)
      })
    },
    getDashboardData({commit}) {
      commit('dashboardLoading', true)
      return axiosClient.get(`/dashboard`)
      .then((res) => {
        commit('dashboardLoading', false)
        commit('setDashboardData', res.data)
        return res;
      })
      .catch(error => {
        commit('dashboardLoading', false)
        return error;
      })

    },
    getSurveys({ commit }, {url = null} = {}) {
      commit('setSurveysLoading', true)
      url = url || "/survey";
      return axiosClient.get(url).then((res) => {
        commit('setSurveysLoading', false)
        commit("setSurveys", res.data);
        return res;
      });
    },
    getSurvey({ commit }, id) {
      commit("setCurrentSurveyLoading", true);
      return axiosClient
        .get(`/survey/${id}`)
        .then((res) => {
          commit("setCurrentSurvey", res.data);
          commit("setCurrentSurveyLoading", false);
          return res;
        })
        .catch((err) => {
          commit("setCurrentSurveyLoading", false);
          throw err;
        });
    },
    getSurveyBySlug({ commit }, slug) {
      commit("setCurrentSurveyLoading", true);
      return axiosClient
        .get(`/survey-by-slug/${slug}`)
        .then((res) => {
          commit("setCurrentSurvey", res.data);
          commit("setCurrentSurveyLoading", false);
          return res;
        })
        .catch((err) => {
          commit("setCurrentSurveyLoading", false);
          throw err;
        });
    },
    saveSurvey({ commit, dispatch }, survey) {
      delete survey.image_url;

      let response;
      if (survey.id) {
        response = axiosClient
          .put(`/survey/${survey.id}`, survey)
          .then((res) => {
            commit('setCurrentSurvey', res.data)
            return res;
          });
      } else {
        response = axiosClient.post("/survey", survey).then((res) => {
          commit('setCurrentSurvey', res.data)
          return res;
        });
      }

      return response;
    },
    deleteSurvey({ dispatch }, id) {
      return axiosClient.delete(`/survey/${id}`).then((res) => {
        dispatch('getSurveys')
        return res;
      });
    },
    saveSurveyAnswer({commit}, {surveyId, answers}) {
      return axiosClient.post(`/survey/${surveyId}/answer`, {answers});
    },
    loadMoreEvents({ commit, state }, limit) {
      commit('eventsLoading', true);
      const currentPage = state.events.currentPage;
      if(state.events.hasMore) {
        return axiosClient.get(`/events?page=${currentPage}&limit=${limit}`)
          .then((res) => {
            commit('eventsLoading', false);            
            console.log("Appending events:=======>");
            console.log(res.data)
            commit('appendEvents', res.data);
            commit('incrementPage');
            if(res.data.data.length == 0) {
              commit('noMore');
            }
          })
          .catch(error => {
            commit('eventsLoading', false);
            console.error("Error fetching events:", error);
          });
      }else {
        commit('eventsLoading', false);
      }
    },
    markEventAsRead({ commit }, { id, model }) {
      return axiosClient.put(`/events/${id}/${model}/markAsRead`)
        .then((res) => {
          if (res.status === 200) {
            commit('setEventAsRead', { id, model });
          }
          return res;
        })
        .catch(error => {
          return error;
        });
    },
  },
  mutations: {
    logout: (state) => {
      state.user.token = null;
      state.user.data = {};
      sessionStorage.removeItem("TOKEN");
    },

    setUser: (state, user) => {
      state.user.data = user;
    },
    setToken: (state, token) => {
      state.user.token = token;
      sessionStorage.setItem('TOKEN', token);
    },
    dashboardLoading: (state, loading) => {
      state.dashboard.loading = loading;
    },
    setDashboardData: (state, data) => {
      state.dashboard.data = data
    },
    setSurveysLoading: (state, loading) => {
      state.surveys.loading = loading;
    },
    setSurveys: (state, surveys) => {
      state.surveys.links = surveys.meta.links;
      state.surveys.data = surveys.data;
    },
    setCurrentSurveyLoading: (state, loading) => {
      state.currentSurvey.loading = loading;
    },
    setCurrentSurvey: (state, survey) => {
      state.currentSurvey.data = survey.data;
    },
    notify: (state, {message, type}) => {
      state.notification.show = true;
      state.notification.type = type;
      state.notification.message = message;
      setTimeout(() => {
        state.notification.show = false;
      }, 3000)
    },
    eventsLoading: (state, loading) => {
      state.events.loading = loading;
    },
    appendEvents(state, newEvents) {
      state.events.data.push(...newEvents.data);
      state.events.lastPage = newEvents.data.last_page;
    },
    incrementPage(state) {
      state.events.currentPage++;
    },
    noMore(state) {
      state.events.hasMore = false;
    },
    setEventAsRead(state, { id, model }) {
      const eventIndex = state.events.data.findIndex(e => e.id === id && e.model_name === model);
      if (eventIndex !== -1) {
        const event = { ...state.events.data[eventIndex], is_read: 1 }; 
        state.events.data.splice(eventIndex, 1, event); 
      }
    },
  },
  modules: {},
});

export default store;
