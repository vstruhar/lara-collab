import axios from 'axios';
import { produce } from 'immer';

let historyRequestId = 0;

const createTaskHistorySlice = (set) => ({
  history: [],
  historyHasMore: false,
  fetchHistory: async (task, page, onFinish) => {
    const requestId = ++historyRequestId;

    try {
      const { data } = await axios.get(route('projects.tasks.history', [task.project_id, task.id]), {
        params: { page },
        progress: false,
      });

      if (requestId !== historyRequestId) {
        return;
      }

      set(produce((state) => {
        state.history = page === 1 ? data.data : [...state.history, ...data.data];
        state.historyHasMore = Boolean(data.has_more);
      }));
      onFinish?.();
    } catch (e) {
      if (requestId !== historyRequestId) {
        return;
      }

      onFinish?.(e);
      console.error(e);
      alert('Failed to load history');
    }
  },
});

export default createTaskHistorySlice;
