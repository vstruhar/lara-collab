import { onUploadProgress } from '@/utils/axios';
import { move, reorder } from '@/utils/reorder';
import axios from 'axios';
import { produce } from "immer";
import { create } from 'zustand';

const useTasksStore = create((set, get) => ({
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

    axios
      .post(route("projects.tasks.reorder", [route().params.project]), { ids: result.map((i) => i.id) }, {progress: false})
      .catch(() => alert("Failed to save task reorder action"));

    return set(produce(state => { state.tasks[sourceGroupId] = result }));
  },
  moveTask: (source, destination) => {
    const sourceGroupId = +source.droppableId.split("-")[1];
    const destinationGroupId = +destination.droppableId.split("-")[1];

    const result = move(get().tasks, sourceGroupId, destinationGroupId, source, destination);

    axios
      .post(route("projects.tasks.move", [route().params.project]), {
        group_id: destinationGroupId,
        ids: result[destinationGroupId].map((i) => i.id),
      }, {progress: false})
      .catch(() => alert("Failed to save task move action"));

    return set(produce(state => {
      state.tasks[sourceGroupId] = result[sourceGroupId];
      state.tasks[destinationGroupId] = result[destinationGroupId];
    }));
  },
  uploadAttachments: async (task, files) => {
    const index = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      const { data } = await axios.postForm(
        route("projects.tasks.attachment.upload", [task.project_id, task.id]),
        { attachments: files.filter(i => i.id === undefined) },
        {onUploadProgress}
      );

      return set(produce(state => {
        state.tasks[task.group_id][index].attachments = [
          ...state.tasks[task.group_id][index].attachments,
          ...data.files,
        ];
      }));
    } catch (error) {
      console.warn(error);
      alert("Failed to upload attachments");
    }
  },
  deleteAttachment: async (task, index) => {
    const taskIndex = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      const deleteId = get().tasks[task.group_id][taskIndex].attachments[index].id;
      await axios.delete(route("projects.tasks.attachment.destroy", [task.project_id, task.id, deleteId]), {progress: true});

      return set(produce(state => {
        state.tasks[task.group_id][taskIndex].attachments = [
          ...state.tasks[task.group_id][taskIndex].attachments.filter(i => i.id !== deleteId)
        ];
      }));
    } catch (error) {
      console.warn(error);
      alert("Failed to delete attachment");
    }
  },
}));

export default useTasksStore;
