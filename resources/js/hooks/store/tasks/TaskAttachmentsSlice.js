import { onUploadProgress } from '@/utils/axios';
import axios from 'axios';
import { produce } from "immer";

const createTaskAttachmentsSlice = (set, get) => ({
  uploadAttachments: async (task, files) => {
    const index = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      const { data } = await axios.postForm(
        route("projects.tasks.attachments.upload", [task.project_id, task.id]),
        { attachments: files.filter(i => i.id === undefined) },
        { onUploadProgress }
      );

      return set(produce(state => {
        state.tasks[task.group_id][index].attachments = [
          ...state.tasks[task.group_id][index].attachments,
          ...data.files,
        ];
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to upload attachments");
    }
  },
  deleteAttachment: async (task, index) => {
    const taskIndex = get().tasks[task.group_id].findIndex((i) => i.id === task.id);

    try {
      const deleteId = get().tasks[task.group_id][taskIndex].attachments[index].id;
      await axios.delete(route("projects.tasks.attachments.destroy", [task.project_id, task.id, deleteId]), { progress: true });

      return set(produce(state => {
        state.tasks[task.group_id][taskIndex].attachments = [
          ...state.tasks[task.group_id][taskIndex].attachments.filter(i => i.id !== deleteId)
        ];
      }));
    } catch (e) {
      console.error(e);
      alert("Failed to delete attachment");
    }
  },
});

export default createTaskAttachmentsSlice;
