import usePreferences from "@/hooks/usePreferences";
import TaskCard from "./Task/TaskCard";
import TaskRow from "./Task/TaskRow";

export default function Task({ task, index }) {
  const { tasksView } = usePreferences();

  return tasksView === "list" ? (
    <TaskRow task={task} index={index} />
  ) : (
    <TaskCard task={task} index={index} />
  );
}
