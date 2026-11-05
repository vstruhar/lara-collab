import { move } from "@/utils/reorder";
import { produce } from "immer";

const sameId = (left, right) => Number(left) === Number(right);

const orderTasksByIds = (tasks, taskIds) => {
  const orderedIds = (taskIds ?? []).map((id) => Number(id));
  const tasksById = new Map(tasks.map((task) => [Number(task.id), task]));
  const ordered = [];
  const seen = new Set();

  orderedIds.forEach((id) => {
    const task = tasksById.get(id);

    if (!task || seen.has(id)) {
      return;
    }

    ordered.push(task);
    seen.add(id);
  });

  tasks.forEach((task) => {
    const id = Number(task.id);

    if (seen.has(id)) {
      return;
    }

    ordered.push(task);
    seen.add(id);
  });

  return ordered;
};

const createTaskWebSocketUpdatesSlice = (set, get) => ({
  addTaskLocally: (task) => {
    return set(produce(state => {
      const groupTasks = state.tasks[task.group_id] ?? [];

      if (groupTasks.some((item) => item.id === task.id)) {
        return;
      }

      state.tasks[task.group_id] = [task, ...groupTasks];
    }));
  },
  updateTaskLocally: (taskId, property, value, relatedData = null) => {
    return set(produce(state => {
      const task = get().findTask(taskId);

      if (!task) {
        return;
      }

      const groupTasks = state.tasks[task.group_id];

      if (!groupTasks) {
        return;
      }

      const index = groupTasks.findIndex((item) => item.id === task.id);

      if (index === -1) {
        return;
      }

      if (property === 'group_id' && task.group_id !== value) {
        const result = move(state.tasks, task.group_id, value, index, 0);

        state.tasks[task.group_id] = result[task.group_id];
        state.tasks[value] = result[value];

        state.tasks[value][0][property] = value;
      } else {
        state.tasks[task.group_id][index][property] = value;

        // If related data is provided, update those properties as well
        if (relatedData) {
          Object.keys(relatedData).forEach((key) => {
            state.tasks[task.group_id][index][key] = relatedData[key];
          });
        }
      }
    }));
  },
  removeTaskLocally: (taskId) => {
    return set(produce(state => {
      const task = get().findTask(taskId);

      if (!task) {
        return;
      }

      state.tasks[task.group_id] = state.tasks[task.group_id].filter(i => i.id !== task.id);
    }));
  },
  restoreTaskLocally: (groupId, newTask) => {
    return set(produce(state => {
      if (!newTask) {
        return;
      }

      const groupTasks = state.tasks[groupId] ?? [];

      if (groupTasks.some((task) => sameId(task.id, newTask.id))) {
        return;
      }

      state.tasks[groupId] = [newTask, ...groupTasks].sort((a, b) => (a.order_column > b.order_column ? 1 : -1));
    }));
  },
  addCommentLocally: (comment) => {
    return set(produce(state => {
      if (!comment || state.comments.some((item) => sameId(item.id, comment.id))) {
        return;
      }

      state.comments = [comment, ...state.comments];
    }));
  },
  addAttachmentsLocally: (attachments) => {
    return set(produce(state => {
      const incoming = attachments ?? [];
      const task = incoming.length ? get().findTask(incoming[0].task_id) : null;
      const groupTasks = task ? state.tasks[task.group_id] : null;

      if (!task || !groupTasks) {
        return;
      }

      const index = groupTasks.findIndex((item) => sameId(item.id, task.id));

      if (index === -1) {
        return;
      }

      const existing = groupTasks[index].attachments ?? [];
      const existingIds = new Set(existing.map((attachment) => Number(attachment.id)));
      const nextAttachments = incoming.filter((attachment) => !existingIds.has(Number(attachment.id)));

      groupTasks[index].attachments = [...existing, ...nextAttachments];
    }));
  },
  removeAttachmentLocally: (taskId, attachmentId) => {
    return set(produce(state => {
      const task = get().findTask(taskId);
      const groupTasks = task ? state.tasks[task.group_id] : null;

      if (!task || !groupTasks) {
        return;
      }

      const index = groupTasks.findIndex((item) => sameId(item.id, taskId));

      if (index === -1) {
        return;
      }

      groupTasks[index].attachments = (groupTasks[index].attachments ?? []).filter((item) => !sameId(item.id, attachmentId));
    }));
  },
  addTimeLogLocally: (timeLog) => {
    return set(produce(state => {
      if (!timeLog) {
        return;
      }

      const task = get().findTask(timeLog.task_id);
      const groupTasks = task ? state.tasks[task.group_id] : null;

      if (!task || !groupTasks) {
        return;
      }

      const index = groupTasks.findIndex((item) => sameId(item.id, task.id));

      if (index === -1) {
        return;
      }

      const existing = groupTasks[index].time_logs ?? [];

      if (existing.some((item) => sameId(item.id, timeLog.id))) {
        return;
      }

      groupTasks[index].time_logs = [...existing, timeLog];
    }));
  },
  removeTimeLogLocally: (taskId, timeLogId) => {
    return set(produce(state => {
      const task = get().findTask(taskId);
      const groupTasks = task ? state.tasks[task.group_id] : null;

      if (!task || !groupTasks) {
        return;
      }

      const index = groupTasks.findIndex((item) => sameId(item.id, taskId));

      if (index === -1) {
        return;
      }

      groupTasks[index].time_logs = (groupTasks[index].time_logs ?? []).filter((item) => !sameId(item.id, timeLogId));
    }));
  },
  reorderTaskLocally: (groupId, taskIds) => {
    return set(produce(state => {
      const groupTasks = state.tasks[groupId];

      if (!Array.isArray(groupTasks)) {
        return;
      }

      state.tasks[groupId] = orderTasksByIds(groupTasks, taskIds);
    }));
  },
  moveTaskLocally: (fromGroupId, toGroupId, taskIds, taskId) => {
    return set(produce(state => {
      const sourceTasks = state.tasks[fromGroupId] ?? [];
      const destinationTasks = state.tasks[toGroupId] ?? [];
      const sourceIndex = sourceTasks.findIndex((task) => sameId(task.id, taskId));
      let movedTask = null;

      if (sourceIndex !== -1) {
        movedTask = { ...sourceTasks[sourceIndex], group_id: Number(toGroupId) };
        state.tasks[fromGroupId] = sourceTasks.filter((task) => !sameId(task.id, taskId));
      }

      const remainingDestination = destinationTasks.filter((task) => !sameId(task.id, taskId));
      const pool = movedTask ? [...remainingDestination, movedTask] : remainingDestination;

      if (pool.length === 0 && state.tasks[toGroupId] === undefined) {
        return;
      }

      state.tasks[toGroupId] = orderTasksByIds(pool, taskIds);
    }));
  },
  replaceGroupTasksLocally: (groupId, tasks) => {
    return set(produce(state => {
      const incoming = Array.isArray(tasks) ? tasks : [];
      const existing = state.tasks[groupId] ?? [];
      const seen = new Set();
      const merged = [];

      [...incoming, ...existing].forEach((task) => {
        if (!task || seen.has(Number(task.id))) {
          return;
        }

        seen.add(Number(task.id));
        merged.push(task);
      });

      state.tasks[groupId] = merged.sort((left, right) => (left.order_column > right.order_column ? 1 : -1));
    }));
  },
});

export default createTaskWebSocketUpdatesSlice;
