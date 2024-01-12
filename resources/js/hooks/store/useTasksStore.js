import createTaskAttachmentsSlice from '@/hooks/store/tasks/TaskAttachmentsSlice';
import createTaskCommentsSlice from '@/hooks/store/tasks/TaskCommentsSlice';
import createTaskTimeLogsSlice from '@/hooks/store/tasks/TaskTimeLogsSlice';
import createTaskWebSocketUpdatesSlice from '@/hooks/store/tasks/TaskWebSocketUpdatesSlice';
import { move, reorder } from '@/utils/reorder';
import axios from 'axios';
import { produce } from "immer";
import { create } from 'zustand';

const useTasksStore = create((set, get) => ({
  ...createTaskAttachmentsSlice(set, get),
  ...createTaskTimeLogsSlice(set, get),
  ...createTaskCommentsSlice(set, get),
  ...createTaskWebSocketUpdatesSlice(set, get),

  tasks: {},
  setTasks: (tasks) => set(() => ({ tasks: { ...tasks } })),
  addTask: (task) => {
    return set(produce(state => {
      const index = state.tasks[task.group_id].findIndex((i) => i.id === task.id);

      if (index === -1) {
        state.tasks[task.group_id] = [...state.tasks[task.group_id], task];
      }
    }));
  },
  findTask: (id) => {
    for (const groupId in get().tasks) {
      const task = get().tasks[groupId].find((i) => i.id === id);

      if (task) {
        return task;
      }
    }
    return null;
  },
  updateTaskProperty: async (task, property, value, options = null) => {
    try {
      await axios
        .put(
          route("projects.tasks.update", [task.project_id, task.id]),
          { [property]: value },
          { progress: false },
        );

      return set(produce(state => {
        const index = state.tasks[task.group_id].findIndex((i) => i.id === task.id);

        if (property === 'group_id' && task.group_id !== value) {
          const result = move(state.tasks, task.group_id, value, index, 0);

          state.tasks[task.group_id] = result[task.group_id];
          state.tasks[value] = result[value];

          state.tasks[value][0][property] = value;
        } else {
          state.tasks[task.group_id][index][property] = options || value;
        }
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to save task property change");
    }
  },
  complete: (task, checked) => {
    const newState = checked ? true : null;
    const index = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    axios
      .post(route("projects.tasks.complete", [task.project_id, task.id]), { completed: checked })
      .catch(() => alert("Failed to save task completed action"));

    return set(produce(state => {
      state.tasks[task.group_id][index].completed_at = newState
    }));
  },
  reorderTask: (source, destination) => {
    const sourceGroupId = +source.droppableId.split("-")[1];

    const result = reorder(get().tasks[sourceGroupId], source.index, destination.index);

    const data = {
      ids: result.map((i) => i.id),
      group_id: sourceGroupId,
      from_index: source.index,
      to_index: destination.index,
    };

    axios
      .post(route("projects.tasks.reorder", [route().params.project]), data, { progress: false })
      .catch(() => alert("Failed to save task reorder action"));

    return set(produce(state => { state.tasks[sourceGroupId] = result }));
  },
  moveTask: (source, destination) => {
    const sourceGroupId = +source.droppableId.split("-")[1];
    const destinationGroupId = +destination.droppableId.split("-")[1];

    const result = move(get().tasks, sourceGroupId, destinationGroupId, source.index, destination.index);

    const data = {
      ids: result[destinationGroupId].map((i) => i.id),
      from_group_id: sourceGroupId,
      to_group_id: destinationGroupId,
      from_index: source.index,
      to_index: destination.index,
    };

    axios
      .post(route("projects.tasks.move", [route().params.project]), data, { progress: false })
      .catch(() => alert("Failed to save task move action"));

    return set(produce(state => {
      state.tasks[sourceGroupId] = result[sourceGroupId];
      state.tasks[destinationGroupId] = result[destinationGroupId];
    }));
  },
}));

export default useTasksStore;
