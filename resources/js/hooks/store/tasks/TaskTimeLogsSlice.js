import { convertToMinutes } from '@/utils/timer';
import axios from 'axios';
import { produce } from "immer";

const createTaskTimeLogsSlice = (set, get) => ({
  saveTimeLog: async (task, value) => {
    const index = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      const { data } = await axios.post(
        route("projects.tasks.time-logs.store", [task.project_id, task.id]),
        { minutes: convertToMinutes(value) },
        { progress: true }
      );

      return set(produce(state => {
        state.tasks[task.group_id][index].time_logs = [
          ...state.tasks[task.group_id][index].time_logs,
          data.timeLog,
        ];
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to save time log");
    }
  },
  deleteTimerLog: async (task, deleteId) => {
    const taskIndex = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      await axios.delete(route("projects.tasks.time-logs.destroy", [task.project_id, task.id, deleteId]), { progress: true });

      return set(produce(state => {
        state.tasks[task.group_id][taskIndex].time_logs = [
          ...state.tasks[task.group_id][taskIndex].time_logs.filter(i => i.id !== deleteId)
        ];
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to delete time log");
    }
  },
  startTimer: async (task) => {
    const index = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      const { data } = await axios.post(route("projects.tasks.time-logs.timer.start", [task.project_id, task.id]), {}, {progress: true});

      return set(produce(state => {
        state.tasks[task.group_id][index].time_logs = [
          ...state.tasks[task.group_id][index].time_logs,
          data.timeLog,
        ];
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to start timer");
    }
  },
  stopTimer: async (task, timeLogId) => {
    const taskIndex = get().tasks[task.group_id].findIndex((i) => i.id === task.id);
    const index = get().tasks[task.group_id][taskIndex].time_logs.findIndex((i) => i.id === timeLogId);

    try {
      const { data } = await axios.post(route("projects.tasks.time-logs.timer.stop", [task.project_id, task.id, timeLogId]), {}, {progress: true});

      return set(produce(state => {
        state.tasks[task.group_id][taskIndex].time_logs[index] = {...data.timeLog};
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to stop timer");
    }
  },
});

export default createTaskTimeLogsSlice;
