import { move, reorder } from '@/utils/reorder';
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
    reorderTask: (source, destination) => {
      const sourceGroupId = +source.droppableId.split("-")[1];

      const result = reorder(get().tasks[sourceGroupId], source.index, destination.index);

      axios.post(route("projects.tasks.reorder", [route().params.project]), {
        ids: result.map((i) => i.id),
      });

      return set(produce(state => {state.tasks[sourceGroupId] = result}));
    },
    moveTask: (source, destination) => {
      const sourceGroupId = +source.droppableId.split("-")[1];
      const destinationGroupId = +destination.droppableId.split("-")[1];

      const result = move(get().tasks, sourceGroupId, destinationGroupId, source, destination);

      axios.post(route("projects.tasks.move", [route().params.project]), {
          group_id: destinationGroupId,
          ids: result[destinationGroupId].map((i) => i.id),
      });

      return set(produce(state => {
        state.tasks[sourceGroupId] = result[sourceGroupId];
        state.tasks[destinationGroupId] = result[destinationGroupId];
      }));
    },
  }))
);

window.store = useTasksStore;

export default useTasksStore;
