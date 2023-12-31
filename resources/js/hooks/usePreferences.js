import { useLocalStorage } from "@mantine/hooks";

export default function usePreferences() {
  const [tasksView, setTasksView] = useLocalStorage({
    key: "tasks-view",
    defaultValue: "list",
    getInitialValueInEffect: false,
  });

  return {tasksView, setTasksView};
}
