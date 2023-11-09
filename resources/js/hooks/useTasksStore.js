import { reorder } from '@/services/ReorderService';
import axios from 'axios';
import { produce } from "immer";
import zukeeper from 'zukeeper';
import { create } from 'zustand';

const useTasksStore = create(zukeeper((set, get) => ({
    groups: [],
    tasks: {},
    setGroups: (groups) => set(() => ({ groups: [...groups] })),
    setTasks: (tasks) => set(() => ({ tasks: {...tasks} })),
    complete: (task, checked) => {
      const newState = checked ? true : null;
      const index = get().tasks[task.task_group_id].findIndex((i) => i.id === task.id);

      axios
        .post(route("projects.tasks.complete", [task.project_id, task.id]), {
          completed: checked,
        })
        .catch(() => alert("Failed to save task completed action"));

      return set(produce(state => {state.tasks[task.task_group_id][index].completed_at = newState}));
    },
    reorderGroup: (fromIndex, toIndex) => {
      const result = reorder(get().groups, fromIndex, toIndex);

      axios.post(route("projects.task-groups.reorder", route().params.project), {
        ids: result.map((i) => i.id),
      });

      return set(() => ({groups: [...result]}));
    },
    reorderTask: (groupId, fromIndex, toIndex) => {
      const result = reorder(get().tasks[groupId], fromIndex, toIndex);

      axios.post(route("projects.tasks.reorder", [route().params.project]), {
        ids: result.map((i) => i.id),
      });

      return set(produce(state => {state.tasks[groupId] = result}));
    },
  }))
);

window.store = useTasksStore;

export default useTasksStore;
