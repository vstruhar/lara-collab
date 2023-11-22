import { move } from "@/utils/reorder";
import { produce } from "immer";

const createTaskWebSocketUpdatesSlice = (set, get) => ({
  updateTaskLocally: (task, data) => {
    return set(produce(state => {
      const index = state.tasks[task.group_id].findIndex((i) => i.id === task.id);

      if (task.group_id !== data.group_id) {
        const result = move(state.tasks, task.group_id, data.group_id, index, 0);

        state.tasks[task.group_id] = result[task.group_id];
        state.tasks[data.group_id] = result[data.group_id];

        state.tasks[data.group_id][0] = {
          ...state.tasks[data.group_id][0],
          ...data,
        }
      } else {
        state.tasks[task.group_id][index] = {
          ...state.tasks[task.group_id][index],
          ...data,
        }
      }
    }));
  },
  archiveTaskLocally: (task) => {
    return set(produce(state => {
      state.tasks[task.group_id] = state.tasks[task.group_id].filter(i => i.id !== task.id);
    }));
  },
  restoreTaskLocally: (groupId, newTask) => {
    return set(produce(state => {
      state.tasks[groupId] = [newTask, ...state.tasks[groupId]].sort((a, b) => (a.order_column > b.order_column ? 1 : -1));
    }));
  },
});

export default createTaskWebSocketUpdatesSlice;
