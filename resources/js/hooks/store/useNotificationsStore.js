import axios from 'axios';
import dayjs from 'dayjs';
import { produce } from 'immer';
import { create } from 'zustand';

const useNotificationsStore = create((set) => ({
  notifications: [],
  setNotifications: (notifications) => {
    return set(produce(state => {
      state.notifications = [...notifications];
    }));
  },
  addNotification: (notification) => {
    return set(produce(state => {
      const oldNotifications = state.notifications.length >= 6
        ? state.notifications.slice(0, state.notifications.length - 1)
        : state.notifications;

      state.notifications = [notification, ...oldNotifications];
    }));
  },
  markAsRead: async (notification) => {
    try {
      await axios.put(route("notifications.read", notification.id));

      return set(produce(state => {
        const index = state.notifications.findIndex(i => i.id === notification.id);
        state.notifications[index].read_at = dayjs().toISOString();
      }));
    } catch (e) {
      console.warn("Failed to set notification as read", e);
    }
  },
  markAllAsRead: async () => {
    try {
      await axios.put(route("notifications.read.all"));

      return set(produce(state => {
        state.notifications.forEach(notification => {
          notification.read_at = dayjs().toISOString();
        });
      }));
    } catch (e) {
      console.warn("Failed to set notifications as read", e);
    }
  },
}));

export default useNotificationsStore;
