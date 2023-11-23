import { move } from "@/utils/reorder";
import { produce } from "immer";

const createTaskWebSocketUpdatesSlice = (set, get) => ({
  addTaskLocally: (task) => {
    return set(produce(state => {
      state.tasks[task.group_id] = [task, ...state.tasks[task.group_id]];
    }));
  },
  updateTaskLocally: (newTask) => {
    return set(produce(state => {
      const task = get().findTask(newTask.id);
      const index = state.tasks[task.group_id].findIndex((i) => i.id === task.id);

      if (task.group_id !== newTask.group_id) {
        const result = move(state.tasks, task.group_id, newTask.group_id, index, 0);

        state.tasks[task.group_id] = result[task.group_id];
        state.tasks[newTask.group_id] = result[newTask.group_id];

        state.tasks[newTask.group_id][0] = {
          ...state.tasks[newTask.group_id][0],
          ...newTask,
        }
      } else {
        state.tasks[task.group_id][index] = {
          ...state.tasks[task.group_id][index],
          ...newTask,
        }
      }
    }));
  },
  removeTaskLocally: (taskId) => {
    return set(produce(state => {
      const task = get().findTask(taskId);

      state.tasks[task.group_id] = state.tasks[task.group_id].filter(i => i.id !== task.id);
    }));
  },
  restoreTaskLocally: (groupId, newTask) => {
    return set(produce(state => {
      state.tasks[groupId] = [newTask, ...state.tasks[groupId]].sort((a, b) => (a.order_column > b.order_column ? 1 : -1));
    }));
  },
  addCommentLocally: (comment) => {
    return set(produce(state => {
      state.comments = [comment, ...state.comments];
    }));
  },
  addAttachmentsLocally: (attachments) => {
    return set(produce(state => {
      const task = get().findTask(attachments[0].task_id);
      const index = state.tasks[task.group_id].findIndex(i => i.id === task.id);

      state.tasks[task.group_id][index].attachments = [...state.tasks[task.group_id][index].attachments, ...attachments];
    }));
  },
  removeAttachmentLocally: (taskId, attachmentId) => {
    return set(produce(state => {
      const task = get().findTask(taskId);
      const index = state.tasks[task.group_id].findIndex(i => i.id === taskId);

      state.tasks[task.group_id][index].attachments = state.tasks[task.group_id][index].attachments.filter(i => i.id !== attachmentId);
    }));
  },
  addTimeLogLocally: (timeLog) => {
    return set(produce(state => {
      console.log(timeLog);
      const task = get().findTask(timeLog.task_id);
      const index = state.tasks[task.group_id].findIndex(i => i.id === task.id);

      state.tasks[task.group_id][index].time_logs = [...state.tasks[task.group_id][index].time_logs, timeLog];
    }));
  },
  removeTimeLogLocally: (taskId, timeLogId) => {
    return set(produce(state => {
      const task = get().findTask(taskId);
      const index = state.tasks[task.group_id].findIndex(i => i.id === taskId);

      state.tasks[task.group_id][index].time_logs = state.tasks[task.group_id][index].time_logs.filter(i => i.id !== timeLogId);
    }));
  },
});

export default createTaskWebSocketUpdatesSlice;
