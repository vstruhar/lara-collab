import { produce } from "immer";

const createTaskWebSocketUpdatesSlice = (set, get) => ({
  addTaskGroupLocally: (taskGroup) => {
    return set(produce(state => {
      state.groups = [...state.groups, taskGroup];
    }));
  },
  updateTaskGroupLocally: (taskGroup) => {
    return set(produce(state => {
      const index = get().groups.findIndex(i => i.id === taskGroup.id);
      state.groups[index] = taskGroup;
    }));
  },
  removeTaskGroupLocally: (taskGroupId) => {
    return set(produce(state => {
      state.groups = state.groups.filter(i => i.id !== taskGroupId);
    }));
  },
  restoreTaskGroupLocally: (taskGroup) => {
    return set(produce(state => {
      state.groups = [
        taskGroup,
        ...state.groups.filter(i => i.id !== taskGroup.id)
      ].sort((a, b) => (a.order_column > b.order_column ? 1 : -1));
    }));
  },
  reorderTaskGroupLocally: (ids) => {
    return set(produce(state => {
      state.groups = state.groups.sort((a, b) => ids.indexOf(a.id) - ids.indexOf(b.id));
    }));
  },
});

export default createTaskWebSocketUpdatesSlice;
