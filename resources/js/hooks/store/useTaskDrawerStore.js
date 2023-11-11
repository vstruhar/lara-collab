import { produce } from 'immer';
import { create } from 'zustand';

const useTaskDrawerStore = create((set, get) => ({
  create: {
    opened: false,
    group_id: null,
  },
  edit: {
    opened: false,
  },
  openCreateTask: (groupId = null) => {
    return set(produce(state => {
      state.create.opened = true;
      state.create.group_id = groupId;
    }));
  },
  closeCreateTask: () => {
    return set(produce(state => {
      state.create.opened = false;
      state.create.group_id = null;
    }));
  },
}));

export default useTaskDrawerStore;
