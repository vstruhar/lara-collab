export const getMessage = (notification) => {
  if (notification.type.includes("CommentCreated")) {
    return {
      title: `${notification.payload.created_by} commented on "${notification.payload.task.name}"`,
      message: `On "${notification.payload.project.name}" project`,
    };
  }
  if (notification.type.includes("TaskCreated")) {
    return {
      title: `${notification.payload.created_by} created a new task`,
      message: `On "${notification.payload.project.name}" project`,
    };
  }
};
