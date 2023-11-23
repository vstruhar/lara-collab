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
  findTask: (id) => {
    for (const groupId in get().tasks) {
      const task = get().tasks[groupId].find((i) => i.id === id);

      if (task) {
        return task;
      }
    }
    return null;
  },
  updateTask: async (task, data) => {
    try {
      const response = await axios
        .put(
          route("projects.tasks.update", [task.project_id, task.id]),
          data,
          { progress: false },
        );

      return set(produce(state => {
        const index = state.tasks[task.group_id].findIndex((i) => i.id === task.id);

        if (task.group_id !== data.group_id) {
          const result = move(state.tasks, task.group_id, data.group_id, index, 0);

          state.tasks[task.group_id] = result[task.group_id];
          state.tasks[data.group_id] = result[data.group_id];

          state.tasks[data.group_id][0] = {
            ...state.tasks[data.group_id][0],
            ...response.data.task,
          }
        } else {
          state.tasks[task.group_id][index] = {
            ...state.tasks[task.group_id][index],
            ...response.data.task,
          }
        }
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to save task changes");
    }
  },
  complete: (task, checked) => {
    const newState = checked ? true : null;
    const index = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    axios
      .post(route("projects.tasks.complete", [task.project_id, task.id]), { completed: checked })
      .catch(() => alert("Failed to save task completed action"));

    return set(produce(state => { state.tasks[task.group_id][index].completed_at = newState }));
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
