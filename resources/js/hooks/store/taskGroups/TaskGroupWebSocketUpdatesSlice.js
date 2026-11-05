import { produce } from "immer";

const createTaskWebSocketUpdatesSlice = (set) => ({
  addTaskGroupLocally: (taskGroup) => {
    return set(produce(state => {
      if (!taskGroup || state.groups.some((group) => group.id === taskGroup.id)) {
        return;
      }

      state.groups = [...state.groups, taskGroup];
    }));
  },
  updateTaskGroupLocally: (taskGroup) => {
    return set(produce(state => {
      const index = state.groups.findIndex((group) => group.id === taskGroup?.id);

      if (index === -1) {
        return;
      }

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
      if (!taskGroup) {
        return;
      }

      state.groups = [
        taskGroup,
        ...state.groups.filter((group) => group.id !== taskGroup.id),
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
