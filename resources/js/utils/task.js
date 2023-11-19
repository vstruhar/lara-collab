import dayjs from "dayjs";

export const isOverdue = (task) => {
  return dayjs().isAfter(task.due_on);
};
