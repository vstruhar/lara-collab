import createTaskGroupWebSocketUpdatesSlice from '@/hooks/store/taskGroups/TaskGroupWebSocketUpdatesSlice';
import { reorder } from '@/utils/reorder';
import axios from 'axios';
import { create } from 'zustand';

const useTaskGroupsStore = create((set, get) => ({
  ...createTaskGroupWebSocketUpdatesSlice(set, get),

  groups: [],
  setGroups: (groups) => set(() => ({ groups: [...groups] })),
  findTaskGroup: (id) => {
    return get().groups.find((i) => i.id === id);
  },
  reorderGroup: (fromIndex, toIndex) => {
    const result = reorder(get().groups, fromIndex, toIndex);

    axios
      .post(route("projects.task-groups.reorder", route().params.project), {ids: result.map((i) => i.id)})
      .catch(() => alert("Failed to save task group reorder action"));

    return set(() => ({ groups: [...result] }));
  },
}));

export default useTaskGroupsStore;
